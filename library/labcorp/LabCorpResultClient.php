<?php
/** **************************************************************************
 *	LabCorpResultClient.PHP
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
require_once 'LabCorpParserHL7v2.php';

// must have phpseclib in path
$current_path = get_include_path();
if (strpos($current_path, 'phpseclib') === false)
	set_include_path($current_path . PATH_SEPARATOR . "{$GLOBALS['srcdir']}/labcorp/phpseclib");

// include necessary libraries
include('Net/SSH2.php');
include('Net/SFTP.php');

if (!class_exists("LabCorpResultClient")) {
	/**
	 * class LabCorpResultClient submits lab order (HL7 messages) to the MedPlus Hub
	 * platform.  Encapsulates the sending of an HL7 order to a LabCorp Lab
	 * via the Hub’s SOAP Web service.
	 *	
	 */
	class LabCorpResultClient {
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
		// message to Hub
		private $SENDING_APPLICATION = "";

		// SENDING_FACILITY designates the account number provided to you by LabCorp
		// for the businessunit you are ordering tests with
		private $SENDING_FACILITY = "";
		
		// RECEIVING_FACILITY designates the business unit within LabCorp from which
		// the labs are being ordered
		private $RECEIVING_FACILITY = "";
		
		// data storage   	
    	private $request = null;
    	private $response = null;
    	private $messages = array();
    	private $documents = array();
    	
    	/**
		 * Constructor for the 'result client' class.
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
    	
		/**
		 *
	 	 * Retrieve result 
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create an Result request object which contains HL7 Result parameters
		 * 4. Output response valuse to console.
		 *
		 */
		public function getResults($max = 1, $DEBUG = false) {
			$response = null;
			$results = array();
			$more_results = false;
			$this->messages = array();

			// result directory
			$rdir = "/results/";
			$ldir = "{$GLOBALS["OE_SITE_DIR"]}/lab/";
				
			if ($GLOBALS['lab_corp_status'] == 'T') { // don't send development orders
				echo "\n\nStatus: TRAINING \n";
				echo "Message: Result not retrieved from LabCorp interface \n";
				return $this->messages;
			}

			// anything already waiting? requeue them
			$old = 0;
			$new = 0;
			$odir = scandir($ldir); // return all contents
			if ($odir) {
				foreach ($odir AS $ofile) {
					if (strpos(strtoupper($ofile),'.DAT') === false) continue;
						$results[] = $ofile;
						$old++;
				}
			}
			echo "\n".$old." Existing Records";
				
			try {
				// scanity check before doing anything
				if ( isset($this->USERNAME) && isset($this->PASSWORD) && isset($this->ENDPOINT)) {
					if (!file_exists($ldir)) {
						throw new Exception("Missing working lab results directory!!");
					}
						
					$sftp = new Net_SFTP($this->ENDPOINT,$this->PORT);
					if (!$sftp->login($this->USERNAME, $this->PASSWORD)) {
						throw new Exception("sFTP session did not initialize!!");
					}
			
					// get result content list
					$sftp->chdir($rdir);
					$newdir = $sftp->pwd();
					$rlist = $sftp->rawlist();
					
					// get results
					if ($rlist) {
						foreach ($rlist AS $fname => $fattr) {
							if (strpos(strtoupper($fname),'.DAT') === false || $fattr['type'] != NET_SFTP_TYPE_REGULAR) continue; // not a result file
							if ($new < $max) {
								// store the contents of the result file
								$new++;
								$results[] = $fname;
								if ($sftp->get($fname,$ldir.$fname) === false) {
									throw new Exception("Encountered while retrieving '$fname' from server!!");
								}
							}
							else { // stop fetching and just count records
								$more_results = true;
							}
						}
					}
				}
				
				echo "\n".$new." Results Returned";
				if ($more_results) echo " (MORE RESULTS)";
				if ($DEBUG) {
					if (count($results)) echo "\nHL7 Messages:";
				}
				
				foreach ($results as $fname) {
					$result = file_get_contents($ldir.$fname);
					if ($result === false) {
						throw new Exception("Failed to read '$fname' from work directory!!");
					}
					
					if ($DEBUG) echo "\n" . $result;
					
					$parser = new Parser_HL7v2($result);
					$parser->parse();
					$message = $parser->getMessage();
					
					$message->message_id = $result->resultId;
					$message->response_id = $response_id;
					$message->file_name = $fname;
					
					// add the message to the results
					$this->messages[] = $message;
				}
			} 
			catch (Exception $e) {
				die("FATAL ERROR: " . $e->getMessage());
			}
			
			return $this->messages;
		}
		
		/**
		 *
	 	 * Repeat processing of result 
		 *
		 */
		public function repeatResults($max = 1, $from = FALSE, $thru = FALSE, $DEBUG = FALSE) {
			$response = null;
			$results = array();
			$more_results = false;
			$this->messages = array();

			// local backup result directory
			$ldir = "{$GLOBALS["OE_SITE_DIR"]}/lab/backup/";

			if ($GLOBALS['lab_corp_status'] == 'D') { // don't send development orders
				echo "\n\nStatus: DEVELOPMENT \n";
				echo "Message: Result not retrieved from LabCorp interface \n";
			}

			try {
				// scanity check before doing anything
				if (!file_exists($ldir)) {
					throw new Exception("Missing backup lab results directory!!");
				}
						
				// get result content list
				$rlist = scandir($ldir); // get dir content list
					
				// get results
				$count = 0;
				if (count($rlist) > 0) {
					foreach ($rlist AS $fname) {
						if (strpos(strtoupper($fname),'.DAT') === false) continue; // not a result file
						$fdate = filemtime($ldir.$fname);
						$last = date('Ymd',$fdate);
						if ($last < $from || $last > $thru) continue; // not is selected range
						
						if ($count < $max) {
							// store the contents of the result file
							$results[$count++] = $fname;
						}
						else { // stop fetching and just count records
							$more_results = true;
							$count++;
						}
					}
				}
				
				echo "\n".count($results)." Results Qualified";
				if ($more_results) echo " (MORE RESULTS)";
				if ($DEBUG) {
					if (count($results)) echo "\nHL7 Messages:";
				}
				
				foreach ($results as $fname) {
					$result = file_get_contents($ldir.$fname);
					if ($result === false) {
						throw new Exception("Failed to read '$fname' from backup lab directory!!");
					}
					
					if ($DEBUG) echo "\n" . $result;
					
					$parser = new Parser_HL7v2($result);
					$parser->parse();
					$message = $parser->getMessage();
					
					$message->message_id = $result->resultId;
					$message->response_id = $response_id;
					$message->file_name = $fname;
					
//					$message->documents = $result->documents;

					// add the message to the results
					$this->messages[] = $message;
				}
			} 
			catch (Exception $e) {
				die("FATAL ERROR: " . $e->getMessage());
			}
			
			return $this->messages;
		}
		
		/**
		 *
	 	 * The setResultAck() method will:
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create an ACK request object
		 * 3. Submit calling Acknowledgment()
		 *
		 */
		public function setResultAck($file, $DEBUG = false) {
			$ldir = "{$GLOBALS["OE_SITE_DIR"]}/lab/";
			try {
				$status = rename ($ldir.$file, $ldir."backup/".$file);
				if ($status === false)
					throw new Exception("ERROR: Setting acknowledgement on file '$file'!!");
			} 
			catch (Exception $e) {
				echo ($e->getMessage());
			}
			
			return;
		}
		
		
		public function getProviderAccounts() {
			$results = array();
			try {
				$results = $this->service->getProviderAccounts();
				echo "\n".count($results)." Results Returned";
				
				echo "\nProviders:";
				var_dump($results);
			} 
			catch (Exception $e) {
				echo($e->getMessage());
			}
			
			return;
		}
		
	}
}
