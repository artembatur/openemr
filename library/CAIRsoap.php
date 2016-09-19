<?php

/**
 * Functions for interacting with CAIR SOAP calls.
 *
 * Copyright (C) 2015-2015 Daniel Pflieger <daniel@mi-squared.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option) any
 * later version.  This program is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.  You should have received a copy of the GNU
 * General Public License along with this program.
 * If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @author     Daniel Pflieger daniel@mi-squared.com>
 * @link       http://www.open-emr.org
 */

class CAIRsoap {

	private $Username;
	private $Password;
	private $Facility;
	private $wsdlUrl;
        private $certs;

	private $soapClient;

	public function setUsername($username) {
		$this->Username = $username;

		return $this;
	}

	public function getUsername() {
		return $this->Username;
	}

	public function setPassword($password) {
		$this->Password = $password;

		return $this;
	}

	public function getPassword() {
		return $this->Password;
	}

	public function setFacility($facility) {
		$this->Facility = $facility;

		return $this;
	}

	public function getFacility() {
		return $this->Facility;
	}

        public function setCerts($certs)
        {
                $this->certs = $certs;
                return $this;
        }
        
        public function getCerts() {
		return $this->certs;
	}

        
        
	public function setWsdlUrl($wsdlUrl) {
		$this->wsdlUrl = $wsdlUrl;

		return $this;
	}

	public function getWsdlUrl() {
		return $this->wsdlUrl;
	}

	public function setFromGlobals($globalsArray) {
		$this->setUsername($globalsArray['username']);
		$this->setPassword($globalsArray['password']);
		$this->setFacility($globalsArray['facility']);
		$this->setWsdlUrl($globalsArray['wsdl_url']);
                $this->setCerts($globalsArray['certs']);

		return $this;
	}

	public function setSoapClient(SoapClient $client) {
		$this->soapClient = $client;

		return $this;
	}

	public function getSoapClient() {
		return $this->soapClient;
	}

	public function initializeSoapClient() {

		//return $this->setSoapClient(new SoapClient($this->getWsdlUrl(), $this->getCerts() ));
		return $this->setSoapClient(new SoapClient($this->getWsdlUrl(), array( 'soap_version' => SOAP_1_2) ));
	}

	public function submitSingleMessage($message) {

		//submitSingleMessage connectivityTest
		try {
			return $this->getSoapClient()->submitSingleMessage(
				array(
					'username'		=> $this->getUsername(),
					'password'		=> $this->getPassword(),
					'facilityID'	=> $this->getFacility(),
					'hl7Message'	=> $message
				)
			);
		}
		catch (Exception $e) {
			echo $e->getMessage();
			exit;
		}
	}

	public function getSentMessage(){



	}
}