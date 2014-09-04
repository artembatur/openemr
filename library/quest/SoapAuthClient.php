<?php
/** **************************************************************************
 *	SoapAuthClient.PHP
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
class SoapAuthClient extends SoapClient {
	/**
	 * Since the PHP SOAP package does not support basic authentication
	 * this class downloads the WDSL file using the cURL package and
	 * creates a local copy of the wdsl on the server.
	 * 
	 * Make sure you provide the following additional parameter in the
	 * $options Array: wdsl_local_copy => true
	 */

//	private $cache_dir = 'C:/Users/criswell/My Projects/SoapTest/cache/';
//	private $cache_url = 'http://localhost/SoapTest/cache/';
	
	function SoapAuthClient($wdsl, $options) {
		if (isset($options['wsdl_local_copy']) &&
				isset($options['login']) &&
				isset($options['password'])) {
			 
			 
//			$file = md5(uniqid()).'.xml';
			$file = "/" . $options['wsdl_local_copy'].'.xml'; 
			$temp = $GLOBALS['temporary_files_dir'];
			 
			if (($fp = fopen($temp.$file, "w+")) == false) {
				throw new Exception('Could not create local WDSL file ('.$temp.$file.')');
			}
			 
			$ch = curl_init();
			$credit = ($options['login'].':'.$options['password']);
			curl_setopt($ch, CURLOPT_URL, $wdsl);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $credit);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 25);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			
			// testing only!!
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			
			if (($xml = curl_exec($ch)) === false) {
				//curl_close($ch);
				fclose($fp);
				unlink($temp.$file);
				 
				throw new Exception(curl_error($ch));
			}
			 
			curl_close($ch);
			fclose($fp);
			$wdsl = "file:///".$temp.$file;
		}
		 
		 
		unset($options['wdsl_local_copy']);
		unset($options['wdsl_force_local_copy']);
		 
//		echo "\n" . $wdsl;
		parent::__construct($wdsl, $options);
	}
}
?>
