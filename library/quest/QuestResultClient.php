<?php
/** **************************************************************************
 *	QuestResultClient.PHP
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
 *  @package quest
 *  @subpackage library
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once 'ObservationResultService.php';
require_once 'QuestParserHL7v2.php';

if (!class_exists("QuestResultClient")) {
	/**
	 * class QuestResultClient submits lab order (HL7 messages) to the MedPlus Hub
	 * platform.  Encapsulates the sending of an HL7 order to a Quest Lab
	 * via the Hub’s SOAP Web service.
	 *	
	 */
	class QuestResultClient {
		/**
		 * Will pass the username and password to establish a service connection to
		 * the hub. Facilitates packaging the order in a proper HL7 format. Performs
		 * the transmission of the order to the Hub's SOAP Web Service. Provides
		 * method calls to the Results Web Service to retrieve lab results.
		 * 
		 */
		private $STATUS = "D"; // development (T=training, P=production)
		private $ENDPOINT = "https://cert.hub.care360.com/observation/result/service?wsdl";
//		https://hubservices.medplus.com/observation/result/service?wsdl
		private $USERNAME = "";
		private $PASSWORD = "";
		
		// SENDING_APPLICATION designates the application that is sending the order
		// message to Hub
		private $SENDING_APPLICATION = "";

		// SENDING_FACILITY designates the account number provided to you by Quest
		// for the businessunit you are ordering tests with
		private $SENDING_FACILITY = "";
		
		// RECEIVING_FACILITY designates the business unit within Quest from which
		// the labs are being ordered
		private $RECEIVING_FACILITY = "";
		
		// data storage   	
		private $service = null;
    	private $request = null;
    	private $response = null;
    	private $messages = array();
    	private $documents = array();
    	
    	/**
		 * Constructor for the 'order client' class which initializes a reference 
		 * to the Quest Hub web service.
		 *
		 * @package QuestWebService
		 * @access public
		 */
		public function __construct() {
			$this->RECEIVING_FACILITY = $GLOBALS['lab_quest_facilityid'];
			$this->SENDING_APPLICATION = $GLOBALS['lab_quest_hubname'];
			$this->SENDING_FACILITY = $GLOBALS['lab_quest_siteid'];
			$this->USERNAME = $GLOBALS['lab_quest_username'];
			$this->PASSWORD = $GLOBALS['lab_quest_password'];
			$this->STATUS = $GLOBALS['lab_quest_status'];
			if ($this->STATUS == 'P')
				$this->ENDPOINT = 'https://hubservices.medplus.com/observation/result/service?wsdl';
				
			$options = array();
			$options['wsdl_local_copy'] = 'wsdl_quest_results';
			$options['login'] = $this->USERNAME;
			$options['password'] = $this->PASSWORD;
		 
			$this->service = new ObservationResultService($this->ENDPOINT,$options);
			$this->request = new ObservationResultRequest();
			$this->response = new ObservationResultResponse();	

			// sanity check
			if ( !$this->RECEIVING_FACILITY ||
					!$this->SENDING_APPLICATION ||
					!$this->SENDING_FACILITY ||
					!$this->USERNAME ||
					!$this->PASSWORD ||
					!$this->ENDPOINT )
				throw new Exception ('Quest Interface Not Properly Configured!!');
			
			return;
		}

		/**
		 * buildRequest() constructs a valid HL7 Order result message string.
	 	 *
	 	 * @access public
	 	 * @param int $max maximum number of result to retrieve
	 	 * @param string[] $data array of order data
	 	 * @return Order $order
	 	 * 
		 */
		public function buildRequest($max_messages = 1, $start_date = false, $end_date = false) {

			$this->request->retrieveFinalsOnly = FALSE;
			$this->request->maxMessages = $max_messages;
			if ($start_date) $this->request->startDate = $start_date;
			if ($end_date) $this->request->endDate = $end_date;
				
			return;
		}
		
		/**
		 *
	 	 * The getResults() method will:
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create an Result request object which contains HL7 Result parameters
		 * 4. Output response valuse to console.
		 *
		 */
		public function getResults($DEBUG = false) {
			$response = null;
			$results = array();
			$response_id = null;
			$more_results = false;
			$this->messages = array();
			
			try {
				$response = $this->service->getResults($this->request);
				$response_id = $response->requestId;
				$more_results = $response->isMore;
				$results = $response->observationResults;
				
				echo "\n".count($results)." Results Returned";
				if ($more_results) echo " (MORE RESULTS)";
//				if ($DEBUG) {
//					if (count($results)) echo "\nHL7 Messages:";
//				}
				
				if ($results) {
					foreach ($results as $result) {
//						if ($DEBUG) echo "\n" . $result->HL7Message;

						$parser = new Parser_HL7v2($result->HL7Message);
						$parser->parse();
						$message = $parser->getRequest();
					
						$message->message_id = $result->resultId;
						$message->response_id = $response_id;
						$message->documents = $result->documents;

						// add the message to the results
						$this->messages[] = $message;
					}
				}
			} 
			catch (Exception $e) {
				echo ($e->getMessage());
			}
			
			return $this->messages;
		}
		
		/**
		 * buildResultAck() constructs a valid HL7 Order result message string.
	 	 *
	 	 * @access public
	 	 * @param int $max maximum number of result to retrieve
	 	 * @param string[] $data array of order data
	 	 * @return Order $order
	 	 * 
		 */
		public function buildResultAck($result_id, $reject = FALSE) {
			$ack = new AcknowledgedResult();
			
			$ack->resultId = $result_id;
			$ack->ackCode = "CA"; // assume okay
			$ack->rejectionReason = '';
			
			if ($reject) {
				$ack->ackCode = "CR"; // reject
				$ack->rejectionReason = $reject;
			}

			return $ack;
		}
		
		/**
		 *
	 	 * The sendResultAck() method will:
	 	 *
		 * 1. Create a proxy for making SOAP calls
		 * 2. Create an ACK request object
		 * 3. Submit calling Acknowledgment()
		 *
		 */
		public function sendResultAck($id, $acks, $DEBUG = false) {
			$response = null;
			$this->request = new Acknowledgment();
			$this->request->requestId = $id;
			$this->request->acknowledgedResults = $acks;
			
			try {
				if ($DEBUG) {
					echo "\n".count($acks)." Result Acknowledgments Sent";
				}
				
				$response = $this->service->acknowledgeResults($this->request);

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
