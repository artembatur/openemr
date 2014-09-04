<?php
/** **************************************************************************
 *	LabCorpWebClient.PHP
 *
 *	Copyright (c)2013 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package labcorp
 *  @subpackage library
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once 'LabCorpWebService.php';
require_once 'LabCorpModelHL7v2.php';
require_once("{$GLOBALS['srcdir']}/classes/Document.class.php");

if (!class_exists("LabCorpWebClient")) {
	/**
	 * The class LabCorpOrderClient submits lab order (HL7 messages) to the LabCorp
	 * platform using SFTP transfer protocol.
	 *	
	 */
	class LabCorpWebClient {
		/**
		 * Will pass the username and password to establish a service connection to
		 * the hub. Facilitates packaging the order in a proper HL7 format. Performs
		 * the transmission of the order to the Hub's SOAP Web Service. Provides
		 * method calls to the Results Web Service to retrieve lab results.
		 * 
		 */
		private $WSDL = '';
		private $STATUS = "D"; // development (T=training, P=production)
		private $USERNAME = "emrp_williamsmedicaltech";
		private $PASSWORD = "zrcBxuzwGu";
		
		// SENDING_APPLICATION designates the application that is sending the order
		private $SENDING_APPLICATION = "";
		
		// SENDING_FACILITY designates the client identifier provided by LabCorp
		private $SENDING_FACILITY = "";

		// RECEIVING_FACILITY designates the business unit within LabCorp
		private $RECEIVING_FACILITY = "";		
		
		// Document storage directory
		private $DOCUMENT_CATEGORY = ""; // labcorp
		private $repository;
		
		private $insurance = array();
		private $orders = array();
		private $request = null;
		private $response = null;
		private $documents = array();

		/**
		 * Constructor for the 'abn web client' class
		 *
		 * @package LabCorpService
		 * @access public
		 */
		public function __construct() {
			$this->WSDL = 'file:///'.$GLOBALS['srcdir'].'/labcorp/production.wsdl.xml';
			$this->DOCUMENT_CATEGORY = $GLOBALS['lab_corp_catid'];
			$this->CLIENT_NUMBER = $GLOBALS['lab_corp_clientid'];
			$this->SENDING_APPLICATION = 'WMTECH'; // williams medical technologies
			$this->STATUS = $GLOBALS['lab_corp_status'];
			if ($this->STATUS == 'P') {
				$this->USERNAME = 'emrp_williamsmedicaltech';
				$this->PASSWORD = 'zrcBxuzwGu';
				$this->WSDL = 'file:///'.$GLOBALS['srcdir'].'/labcorp/production.wsdl.xml';
			}
			
			$this->repository = $GLOBALS['oer_config']['documents']['repository'];
			
			// sanity check
			if ( !$this->DOCUMENT_CATEGORY ||
					!$this->SENDING_APPLICATION ||
					!$this->USERNAME ||
					!$this->PASSWORD ||
					!$this->STATUS ||
					!$this->repository )
				throw new Exception ('LabCorp Web Service Not Properly Configured!!');
			
			$this->service = new LabCorpWebService($this->WSDL,array('trace' => 1));
			$this->request = new AbnRequestType();
			$this->response = new AbnResponseType();	

			
$xml = <<<EOD
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
<wsse:UsernameToken wsu:Id="UsernameToken-29477163" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
<wsse:Username>$this->USERNAME</wsse:Username>
<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#Password">$this->PASSWORD</wsse:Password>
</wsse:UsernameToken>
</wsse:Security>
EOD;
			
			$soapHeader = new SoapHeader('http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', new SoapVar($xml, XSD_ANYXML), true);
			$this->service->__setSoapHeaders(array($soapHeader));
				
			return;
		}
		
		/**
		 *
	 	 * The getAbnDocument() method will:
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create a request object
		 * 3. Submit the reguest
		 * 4. Store response
		 *
		 */
		public function getAbnDocument(&$order_data,&$diag_list,&$test_list) {
			echo "Process: Check Medicare Requirements\n";
				
			// validate the respository directory
			$file_path = $this->repository . preg_replace("/[^A-Za-z0-9]/","_",$order_data->pid) . "/";
			if (!file_exists($file_path)) {
				if (!mkdir($file_path,0700)) {
					throw new Exception("The system was unable to create the directory for this upload, '" . $file_path . "'.\n");
				}
			}
		
			$this->request->accountNumber = $order_data->request_account;
			$this->request->diagCodes = $diag_list; // array of diagnosis
			$this->request->testCodes = $test_list; // array of tests
			$this->request->patientName = $order_data->pat_first." "; 
			if ($order_data->pat_middle) $this->request->patientName .= $order_data->pat_middle." ";
			$this->request->patientName .= $order_data->pat_last;
			$this->request->sourceName = $this->SENDING_APPLICATION;
			
			$abn = new CheckABN();
			$abn->clientCode = $this->CLIENT_NUMBER;
			$abn->IncludePdfInResponse = TRUE;
			$abn->abnRequest = $this->request;
			
			$doc = false;
			$response = null;
			
			try {
				$response = $this->service->checkABN($abn);
				if ($response->responseMsg) {
					foreach ($response->responseMsg as $msg) {
						echo "\nMessage Code: " . $msg->code .
						"\nMessage Type: " . $msg->type .
						"\nMessage Text: " . $msg->text;
					}
				}

//DEBUG				var_dump($response);
				if ($response->isABNRequired) {
					if ($response->abnContents) {
						$unique = date('y').str_pad(date('z'),3,0,STR_PAD_LEFT); // 13031 (year + day of year)
						$docName = $order_data->order_number . "_ABN";
			
						$docnum++;
						$file = $docName."_".$unique.".pdf";
						while (file_exists($file_path.$file)) { // don't overlay duplicate file names
							$docName = $order_data->order_number . "_ABN_".$docnum++;
							$file = $docName."_".$unique.".pdf";
						}
			
						if (($fp = fopen($file_path.$file, "w")) == false) {
							throw new Exception('\nERROR: Could not create local file ('.$file_path.$file.')');
						}
						fwrite($fp,$response->abnContents);
						fclose($fp);

						//DEBUG echo "\nDocument Name: " . $file;

						// register the new document
						$d = new Document();
						$d->name = $docName;
						$d->storagemethod = 0; // only hard disk sorage supported
						$d->url = "file://" .$file_path.$file;
						$d->mimetype = "application/pdf";
						$d->size = filesize($file_path.$file);
						$d->owner = $_SESSION['authUserID'];
						$d->hash = sha1_file( $file_path.$file );
						$d->type = $d->type_array['file_url'];
						$d->set_foreign_id($order_data->pid);
						$d->persist();
						$d->populate();

						$doc = $d; // save for later
							
						// update cross reference
						$query = "REPLACE INTO categories_to_documents set category_id = '".$this->DOCUMENT_CATEGORY."', document_id = '" . $d->get_id() . "'";
						sqlStatement($query);
					}
				}
			} 
			catch (Exception $e) {
				echo htmlspecialchars($this->service->__getLastRequestHeaders());
				echo htmlspecialchars($this->service->__getLastRequest());
				die("FATAL ERROR: " . $e->getMessage());
			}
			
			echo "Status: " . $response->outputType . "\n\n";
			return $doc;
		}
	}	
}
