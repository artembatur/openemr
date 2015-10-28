<?php
/** **************************************************************************
 *	LabCorpWebService.PHP
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

if (!class_exists("EchoRequest")) {
/**
 * EchoRequest
 */
class EchoRequest {
	/**
	 * @access public
	 * @var string
	 */
	public $inString;
}}

if (!class_exists("EchoResponse")) {
/**
 * EchoResponse
 */
class EchoResponse {
	/**
	 * @access public
	 * @var string
	 */
	public $outString;
}}

if (!class_exists("EmrServiceResponse")) {
/**
 * EmrServiceResponse
 */
class EmrServiceResponse {
	/**
	 * @access public
	 * @var boolean
	 */
	public $successful;
	/**
	 * @access public
	 * @var EmrBusinessError
	 */
	public $businessError;
}}

if (!class_exists("EmrBusinessError")) {
/**
 * EmrBusinessError
 */
class EmrBusinessError {
	/**
	 * @access public
	 * @var string
	 */
	public $errorCode;
	/**
	 * @access public
	 * @var string
	 */
	public $errorMessage;
}}

if (!class_exists("CheckABN")) {
/**
 * CheckABN
 */
class CheckABN {
	/**
	 * @access public
	 * @var string
	 */
	public $clientCode;
	/**
	 * @access public
	 * @var AbnRequestType
	 */
	public $abnRequest;
	/**
	 * @access public
	 * @var boolean
	 */
	public $IncludePdfInResponse;
}}

if (!class_exists("AbnRequestType")) {
/**
 * AbnRequestType
 */
class AbnRequestType {
	/**
	 * @access public
	 * @var string
	 */
	public $accountNumber;
	/**
	 * @access public
	 * @var string[]
	 */
	public $diagCodes;
	/**
	 * @access public
	 * @var string[]
	 */
	public $testCodes;
	/**
	 * @access public
	 * @var string
	 */
	public $patientName;
	/**
	 * @access public
	 * @var string
	 */
	public $identificationNumber;
	/**
	 * @access public
	 * @var string
	 */
	public $sourceName;
	/**
	 * @access public
	 * @var string
	 */
	public $sourceVersion;
}}

if (!class_exists("AbnResponseType")) {
/**
 * AbnResponseType
 */
class AbnResponseType {
	/**
	 * @access public
	 * @var boolean
	 */
	public $isABNRequired;
	/**
	 * @access public
	 * @var AbnTest[]
	 */
	public $abnTest;
	/**
	 * @access public
	 * @var Message[]
	 */
	public $message;
	/**
	 * @access public
	 * @var string[]
	 */
	public $nonSpecificDiagnosisCodes;
	/**
	 * @access public
	 * @var string[]
	 */
	public $invalidDiagnosisCodes;
	/**
	 * @access public
	 * @var base64Binary
	 */
	public $abnContents;
	/**
	 * @access public
	 * @var string
	 */
	public $contentType;
	/**
	 * @access public
	 * @var integer
	 */
	public $estCost;
	/**
	 * @access public
	 * @var string
	 */
	public $outputType;
	/**
	 * @access public
	 * @var EmrBusinessError
	 */
	public $businessError;
}}

if (!class_exists("Message")) {
/**
 * Message
 */
class Message {
	/**
	 * @access public
	 * @var string
	 */
	public $code;
	/**
	 * @access public
	 * @var string
	 */
	public $text;
	/**
	 * @access public
	 * @var string
	 */
	public $type;
}}

if (!class_exists("AbnTest")) {
/**
 * AbnTest
 */
class AbnTest {
	/**
	 * @access public
	 * @var AbnCpt[]
	 */
	public $freqCpts;
	/**
	 * @access public
	 * @var AbnCpt[]
	 */
	public $medNecCpts;
	/**
	 * @access public
	 * @var string
	 */
	public $testCode;
	/**
	 * @access public
	 * @var string
	 */
	public $ruoDesc;
	/**
	 * @access public
	 * @var boolean
	 */
	public $ruoDescNotFound;
	/**
	 * @access public
	 * @var boolean
	 */
	public $ruoIndicator;
	/**
	 * @access public
	 * @var integer
	 */
	public $ruoPrice;
	/**
	 * @access public
	 * @var boolean
	 */
	public $ruoPriceNotFound;
	/**
	 * @access public
	 * @var string
	 */
	public $testDesc;
	/**
	 * @access public
	 * @var integer
	 */
	public $testPrice;
	/**
	 * @access public
	 * @var string
	 */
	public $medicareNotPayReason;
}}

if (!class_exists("AbnCpt")) {
/**
 * AbnCpt
 */
class AbnCpt {
	/**
	 * @access public
	 * @var string
	 */
	public $code;
	/**
	 * @access public
	 * @var string
	 */
	public $desc;
	/**
	 * @access public
	 * @var integer
	 */
	public $price;
	/**
	 * @access public
	 * @var boolean
	 */
	public $descNotFound;
	/**
	 * @access public
	 * @var boolean
	 */
	public $priceNotFound;
	/**
	 * @access public
	 * @var integer
	 */
	public $multiplier;
}}

if (!class_exists("Messages")) {
/**
 * Messages
 */
class Messages {
	/**
	 * @access public
	 * @var string
	 */
	public $code;
	/**
	 * @access public
	 * @var string
	 */
	public $text;
	/**
	 * @access public
	 * @var boolean
	 */
	public $error;
}}

if (!class_exists("LabCorpWebService")) {
/**
 * EmrWebServiceService
 * @author WSDLInterpreter
 */
class LabCorpWebService extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"EchoRequest" => "EchoRequest",
		"EchoResponse" => "EchoResponse",
		"EmrServiceResponse" => "EmrServiceResponse",
		"EmrBusinessError" => "EmrBusinessError",
		"CheckABN" => "CheckABN",
		"AbnRequestType" => "AbnRequestType",
		"AbnResponseType" => "AbnResponseType",
		"Message" => "Message",
		"AbnTest" => "AbnTest",
		"AbnCpt" => "AbnCpt",
		"Messages" => "Messages"
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl, $options=array()) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		parent::__construct($wsdl, $options);
	}

	/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: echo
	 * Parameter options:
	 * (EchoRequest) echoRequest
	 * @param mixed,... See function description for parameter options
	 * @return EchoResponse
	 * @throws Exception invalid function signature message
	 */
	public function xecho($mixed = null) {
		$validParameters = array(
			"(EchoRequest)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("echo", $args);
	}


	/**
	 * Service Call: checkABN
	 * Parameter options:
	 * (CheckABN) checkABNRequest
	 * @param mixed,... See function description for parameter options
	 * @return AbnResponseType
	 * @throws Exception invalid function signature message
	 */
	public function checkABN($mixed = null) {
		$validParameters = array(
			"(CheckABN)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("checkABN", $args);
	}


}}

?>