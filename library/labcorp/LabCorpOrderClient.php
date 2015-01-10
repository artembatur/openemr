<?php
/** **************************************************************************
 *	LabCorpOrderClient.PHP
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
require_once 'LabCorpModelHL7v2.php';
require_once 'LabCorpRequest.php';
require_once("{$GLOBALS['srcdir']}/classes/Document.class.php");

// must have phpseclib in path
$current_path = get_include_path();
if (strpos($current_path, 'phpseclib') === false)
	set_include_path($current_path . PATH_SEPARATOR . "{$GLOBALS['srcdir']}/phpseclib");

// include necessary libraries
include('Net/SSH2.php');
include('Net/SFTP.php');

if (!class_exists("LabCorpOrderClient")) {
	/**
	 * The class LabCorpOrderClient submits lab order (HL7 messages) to the LabCorp
	 * platform using SFTP transfer protocol.
	 *	
	 */
	class LabCorpOrderClient {
		/**
		 * Will pass the username and password to establish a service connection to
		 * the hub. Facilitates packaging the order in a proper HL7 format. Performs
		 * the transmission of the order to the Hub's SOAP Web Service. Provides
		 * method calls to the Results Web Service to retrieve lab results.
		 * 
		 */
		private $STATUS = "D"; // development (T=training, P=production)
		private $ENDPOINT = "b2bgateway-staging.labcorp.com";
		private $USERNAME = "";
		private $PASSWORD = "";
		
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
		 * Constructor for the 'order client' class
		 *
		 * @package LabCorpService
		 * @access public
		 */
		public function __construct() {
			$this->DOCUMENT_CATEGORY = $GLOBALS['lab_corp_catid'];
			$this->CLIENT_NUMBER = $GLOBALS['lab_corp_clientid'];
			$this->RECEIVING_APPLICATION = '1100'; // labcorp
			$this->RECEIVING_FACILITY = $GLOBALS['lab_corp_facilityid'];
			$this->SENDING_APPLICATION = 'WMTECH'; // williams medical technologies
			$this->USERNAME = $GLOBALS['lab_corp_username'];
			$this->PASSWORD = $GLOBALS['lab_corp_password'];
			$this->STATUS = $GLOBALS['lab_corp_status'];
			if ($this->STATUS == 'P') $this->ENDPOINT = 'b2bgateway.labcorp.com';
			$this->PORT = 20022;
				
			$this->repository = $GLOBALS['oer_config']['documents']['repository'];
			
			// sanity check
			if ( !$this->DOCUMENT_CATEGORY ||
					!$this->RECEIVING_APPLICATION ||
					!$this->RECEIVING_FACILITY ||
					!$this->SENDING_APPLICATION ||
					!$this->USERNAME ||
					!$this->PASSWORD ||
					!$this->ENDPOINT ||
					!$this->STATUS ||
					!$this->repository )
				throw new Exception ('LabCorp Interface Not Properly Configured!!');
			
			return;
		}

		public function addInsurance($ins) {
			$orderMessage = "IN1|$ins->set_id||$ins->company_id^$ins->labcorp_id|$ins->company_name|$ins->company_address|||$ins->group||||||||$ins->subscriber|$ins->relation||$ins->address||||||||||||$ins->work_flag|||||$ins->policy\r";
			$this->insurance[] = $orderMessage;
		}
		
		public function addOrder($order) {
			$orderMessage = null;

			// save AOE responses
			$aoe = array();
			foreach ($order->aoe as $aoe_data) {
				$aoe[$aoe_data->observation_code] = $aoe_data->observation_text;
			}

			// retrieve provider data
			$user_data = sqlQuery("SELECT * FROM users WHERE id = $order->request_provider LIMIT 1");
			$provider = $user_data['npi']."^".$user_data['lname']."^".$user_data['fname']."^".$user_data['mname'];
			
			// common order segment
			$orderMessage .= "ORC|$order->request_control|$order->request_number||||||||||$provider^^^^N|\r";

			// observation request segment
			$service_id = $order->test_code . "^";
			$service_id .= $order->test_text; // strip specimen text from title
			$service_id .= "^L"; // OBR.04 (^^^6399^CBC)
					
			$orderMessage .= "OBR|$order->set_id|$order->request_number||$service_id|||$order->specimen_datetime||||N||||".$aoe['OBR15']."|$provider^^^^N|\r";
			
			// AOE - Blood lead
			if ($order->zseg == 'LCMBLD') {
				// ZBL message
				$orderMessage .= "ZBL|".$aoe['ZBL1']."|".$aoe['ZBL2']."|".$aoe['ZBL3']."|".$aoe['ZBL4']."|".$aoe['ZBL5']."|\n";
			}
				
			// AOE - Cytology
			if ($order->zseg == 'PAP') {
				// ZCY message
				$orderMessage .= "ZCY|".$aoe['ZCY1']."|".$aoe['ZCY2']."|".$aoe['ZCY3']."|".$aoe['ZCY4']."|".$aoe['ZCY5']."|".$aoe['ZCY6']."|".$aoe['ZCY7']."|".$aoe['ZCY8']."|".$aoe['ZCY9']."|".$aoe['ZCY10']."|".$aoe['ZCY11']."|".$aoe['ZCY12']."|".$aoe['ZCY13']."|".$aoe['ZCY14']."|".$aoe['ZCY15']."|".$aoe['ZCY16']."|".$aoe['ZCY17']."|".$aoe['ZCY18']."|".$aoe['ZCY19']."|".$aoe['ZCY20'];
				$orderMessage .= "|".$aoe['ZCY21']."|".$aoe['ZCY22']."|".$aoe['ZCY23']."|".$aoe['ZCY24']."|".$aoe['ZCY25']."|".$aoe['ZCY26']."|".$aoe['ZCY27']."|".$aoe['ZCY28']."|".$aoe['ZCY29']."|".$aoe['ZCY30']."|".$aoe['ZCY31']."|".$aoe['ZCY32']."|".$aoe['ZCY33']."|".$aoe['ZCY34']."|".$aoe['ZCY35']."|".$aoe['ZCY36']."|".$aoe['ZCY37']."|".$aoe['ZCY38']."|".$aoe['ZCY39']."|".$aoe['ZCY40'];
				$orderMessage .= "|".$aoe['ZCY41']."|".$aoe['ZCY42']."|".$aoe['ZCY43']."|\n";
			}
				
			// AOE - Maternal Screen
			if ($order->zseg == 'MSSNT' || $order->zseg == 'MSONLY' || $order->zseg == 'AFAFP' || $order->zseg == 'SERIN') {
				// ZSA message
				$orderMessage .= "ZSA|".$aoe['ZSA1']."|".$aoe['ZSA2.1']."^".$aoe['ZSA2.2']."^".$aoe['ZSA2.3']."^".$aoe['ZSA2.4']."|".$aoe['ZSA3.1']."^".$aoe['ZSA3.2']."|".$aoe['ZSA4.1']."^".$aoe['ZSA4.2']."|".$aoe['ZSA5.1']."^".$aoe['ZSA5.2']."|".$aoe['ZSA6']."|".$aoe['ZSA7']."|".$aoe['ZSA8']."|".$aoe['ZSA9']."|".$aoe['ZSA10']."|".$aoe['ZSA11']."|".$aoe['ZSA12']."|".$aoe['ZSA13']."|".$aoe['ZSA14'];
				$orderMessage .= "|".$aoe['ZSA15']."|".$aoe['ZSA16']."|".$aoe['ZSA17.1']."^".$aoe['ZSA17.2']."^".$aoe['ZSA17.3']."|".$aoe['ZSA18.1']."^".$aoe['ZSA18.2']."|".$aoe['ZSA19.1']."^".$aoe['ZSA19.2']."^".$aoe['ZSA19.3']."|".$aoe['ZSA20']."|".$aoe['ZSA21']."|".$aoe['ZSA22']."|".$aoe['ZSA23']."|".$aoe['ZSA24']."|".$aoe['ZSA25.1']."^".$aoe['ZSA25.2']."^".$aoe['ZSA25.3'];
				$orderMessage .= "|".$aoe['ZSA26.1']."^".$aoe['ZSA26.2']."^".$aoe['ZSA26.3']."^".$aoe['ZSA26.4']."^".$aoe['ZSA26.5']."^".$aoe['ZSA26.6']."|".$aoe['ZSA27']."|".$aoe['ZSA28']."|\n";
			}
			
			// add order to request message
			$this->orders[] = $orderMessage;
		}
		
		/**
		 * Helper to break comment into line array with max of 60 characters each line
		 * @param string $text
		 * @return array $lines
		 * 
		 */
		private function breakText($text) {
			$lines = array();
			if ($text) {
				$text = str_replace(array("\r\n", "\r", "\n"), " ", $text); // strip newlines
				$text = wordwrap($text,60,'^'); // mark breaks
				$lines = explode('^', $text); // make array
			}
			return $lines;
		}
		
		/**
		 * buildOrderMessage() constructs a valid HL7 Order message string
		 * for the patient and order provided.
	 	 *
	 	 * @access public
	 	 * @param Request $request hl7 data object
	 	 * 
		 */
		public function buildRequest(&$request) {
			// generate message
			$recvr = ($request->facility)? $request->facility: $this->RECEIVING_APPLICATION;
			$MSH = "MSH|^~\\&|%s|$request->request_siteid|%s|%s|$request->datetime||ORM||P|2.3\r";
			$orderMessage = sprintf($MSH, $this->SENDING_APPLICATION, $recvr, $this->RECEIVING_FACILITY);

			$alt_pid = $request->pid;
			if ($request->pubpid && $request->pid != $request->pubpid) $alt_pid = $request->pubpid;
			$PID = "PID|1|$alt_pid||$request->pid|$request->name||$request->dob|$request->sex||$request->race|$request->address||$request->phone|||||$request->account^^^$request->bill_type^$request->abn_signed|||||$request->ethnicity|\r";
			$orderMessage .= $PID;
			
			if ($request->order_notes) {
				$notes = $this->breakText($request->order_notes);
				$seq = 1;
				foreach ($notes AS $note) {
					if ($note) $orderMessage .= "NTE|".$seq++."|P|".$note."|\r"; 
					if ($seq > 10) break; // maximum segments for labcorp
				}	
			}
			
			if ($request->copy_pat || $request->copy_acct || $request->copy_fax) {
				$ZCC = "";
				if ($request->copy_acct) {
					$ZCC .= "A^".$request->copy_acct."^".$request->copy_acctname;
				}
				if ($request->copy_fax) {
					if ($ZCC) $ZCC .= "~";
					$fax = preg_replace( '/[^0-9]/', '', $request->copy_fax);
					$ZCC .= "F^".$fax."^".$request->copy_faxname;
				}
				if ($request->copy_pat)	{
					if ($ZCC) $ZCC .= "~";
					$ZCC .= "P";
				}
				
				$orderMessage .= "ZCC|".$ZCC."\r";
			}
			
				
			foreach ($this->insurance as $ins) {
				$orderMessage .= $ins;
			}
			
			if ($request->bill_type != 'C')
				$orderMessage .="GT1|1||$request->guarantor||$request->guarantor_address|$request->guarantor_phone|||||$request->guarantor_relation|||||$request->guarantor_employer\r";

			// diagnosis segments
			$dx_count = 1;
			foreach ($request->diagnosis as $dx_data) {
				$orderMessage .= "DG1|$dx_count|I9|$dx_data->diagnosis_code^^I9|\r";
				$dx_count++;
			}
				
			$height = ($request->pat_height)? $request->pat_height : "" ;
			$weight = ($request->pat_weight)? $request->pat_weight."^LBS^" : "" ;
			$volume = ($request->order_volume)? $request->order_volume."^ML" : "" ;
			$fasting = ($request->fasting)? $request->fasting: "";
			if ($height || $weight || $volume || $fasting)
				$orderMessage .="ZCI|$height|$weight|$volume|$fasting\r";
			
			foreach ($this->orders as $order) {
				$orderMessage .= $order;
			}
			
			$this->request = $orderMessage;
			//echo $orderMessage . "\n";
							
			return;
		}
		
		/**
		 *
	 	 * The submitOrder() method will:
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create an Order request object which contains a valid HL7 Order message
		 * 3. Submit a Lab Order calling submitOrder().
		 * 4. Output response valuse to console.
		 *
		 */
		public function submitOrder(&$order_data) {
			echo "Process: Submit Electronic Order\n";
			
			$response = null;
			$udir = "/orders/";
			if ($GLOBALS['lab_corp_status'] == 'T') { // don't send development orders
				echo "Status: TRAINING \n";
				echo "Message: Order not sent to LabCorp interface \n";
			}
			else {
				try {
					// create upload file name
					$unique = date('y').str_pad(date('z'),3,0,STR_PAD_LEFT); // 13031 (year + day of year)
					$ufn = $udir."O_".$order_data->labcorp_siteid."_XX_".$order_data->order_number."_".$unique.".DAT";
					
					// scanity check before doing anything 
					if ( isset($this->USERNAME) && isset($this->PASSWORD) && isset($this->ENDPOINT)) {
						$sftp = new Net_SFTP($this->ENDPOINT,$this->PORT);
						if (!$sftp->login($this->USERNAME, $this->PASSWORD)) {
							throw new Exception("sFTP session did not initialize!!");
						}
						// write the file to server
						$sftp->put($ufn, $this->request);
					}
					else {
						die("\n\nFATAL ERROR: missing critical parameters during transmission.");
					}
				}
				catch (Exception $e) {
					die("\n\nFATAL ERROR: ".$e->getMessage());
				}
			}
			
			echo "Status: Success\n\n";
		}
		
		/**
		 *
	 	 * The getOrderDocuments() method will:
	 	 *
		 * 1. Create a PDF requisition document
		 * 2. Store the document in the repository
		 * 3. Return a reference to the document
		 *
	 	 * @access public
	 	 * @param Order $order original order data object
	 	 * @param array $test_list order test information
	 	 * @param array $zseg_list aoe data by zseg 
	 	 * @return int $docId document identifier
		 */
		public function getOrderDocument(&$order_data,&$test_list,&$zseg_list) {
			echo "Process: Generate Documents\n";
			
			// validate the respository directory
			$file_path = $this->repository . preg_replace("/[^A-Za-z0-9]/","_",$order_data->pid) . "/";
			if (!file_exists($file_path)) {
				if (!mkdir($file_path,0700)) {
					throw new Exception("The system was unable to create the directory for this upload, '" . $file_path . "'.\n");
				}
			}
					
			$document = null;
			
			try {
				$document = makeOrderDocument($order_data,$test_list,$zseg_list);
				if ($document) {
					$unique = date('y').str_pad(date('z'),3,0,STR_PAD_LEFT); // 13031 (year + day of year)
					$docName = $order_data->order_number . "_ORDER";
					$file = $docName."_".$unique.".pdf";
						
					$docnum = 0;
					while (file_exists($file_path.$file)) { // don't overlay duplicate file names
						$docName = $order_data->order_number . "_ORDER_".$docnum++;
						$file = $docName."_".$unique.".pdf";
					}
			
					if (($fp = fopen($file_path.$file, "w")) == false) {
						throw new Exception('\nERROR: Could not create local file ('.$file_path.$file.')');
					}
					fwrite($fp,$document);
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

					$doc_data = $d; // save for later
							
					// update cross reference
					$query = "REPLACE INTO categories_to_documents set category_id = '".$this->DOCUMENT_CATEGORY."', document_id = '" . $d->get_id() . "'";
					sqlStatement($query);
				}
			} 
			catch (Exception $e) {
				die("FATAL ERROR ".$e->getMessage());
			}
			
			echo "Status: Success\n\n";
			return $doc_data;
		}
	}
}
