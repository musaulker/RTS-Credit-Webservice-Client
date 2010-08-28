<?php
/**
 * 
 * RTS Credit Webservice Client - PHP 5 class for RTSCredit.com Broker Webservice
 * 
 * PHP Version 5
 * 
 * Required Extensions
 * Soap
 * SimpleXML
 * 
 * The MIT License
 *
 * Copyright (c) 2010 Musa Ulker
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author	Musa Ulker <musaulker@gmail.com>
 * @copyright Musa Ulker 2010
 * @license	  MIT
 * @package   RTSCreditClient
 * @version   1.0
 * @link http://github.com/musaulker/RTS-Credit-Webservice-Client	
 * 
 */
class RTSCreditClient{
	private $_userID;
	private $_userPass;
	private $_endpointURL;
	
	private $_soapClient;
	
	private $_userToken;
	
	private $_userType;
	private $_availableSearches;
	private $_userIsValid;
	
	private $_latestReturnCode;
	private $_latestDescription;
	
	/**
	 * 
	 * Class constructor
	 * 
	 */
	public function __construct() {
		/* check if soap extension is available */
		if(! extension_loaded('soap')){
			echo "I cannot continue because Soap Extension isn't enabled. Sorry. Check this link for how to install & configure: http://tr.php.net/manual/en/book.soap.php \n";
			exit();
		}
		
		/* check if simplexml is available */
		if(! function_exists("simplexml_load_string")){
			echo "I cannot continue because SimpleXML doesn't exist. Sorry. Check this link for how to install & configure: http://php.net/manual/en/book.simplexml.php \n";
			exit();
		}
		
		$this->_endpointURL = "http://webservice.rtscredit.com/CreditReport.asmx?WSDL";
		
		$this->_soapClient = new SoapClient($this->_endpointURL, array("soap_version" => SOAP_1_2, "trace" => false));
	}
	
	/**	
	 * 
	 * Class destructor
	 * 
	 */
	public function __destruct() {
		// empty
	}
	/**
	 * 
	 * Set the webservice proxy
	 * 
	 * @param string $host Proxy hostname or ip
	 * @param integer Proxy port number
	 * @param string Proxy username
	 * @param string Proxy user password
	 * 
	 */
	public function setProxy($host, $port, $login, $password){
		$this->_soapClient = new SoapClient($this->_endpointURL, array(
		"soap_version" => SOAP_1_2, 
		"trace" => false,
		"proxy_host" => $host,
		"proxy_port" => $port,
		"proxy_login" => $login,
		"proxy_password" => $password));
	}
	
	/**
	 * 
	 * set user ID for the webservice login
	 * 
	 * @param string $userID	userID for webservice login
	 * 
	 */
	public function setUserID($userID) {
		$this->_userID = $userID;
	}
	
	/**
	 * 
	 * set user password for the webservice login
	 * 
	 * @param $userPass	user password for the webservice login
	 * 
	 */
	public function setUserPass($userPass) {
		$this->_userPass = $userPass;
	}
	
	/**
	 * 
	 * Method to login to the RTS Credit Web Service and receive a session token. 
	 * This token must be used when calling any other methods in this service and will expire in 24 hours.
	 * 
	 * @return boolean true on success, false on failure
	 * 
	 */
	public function login() {
		try{
			$loginResponse = $this->_soapClient
				->__soapCall("Login", array("Login" => array("UserID" => $this->_userID, "UserPass" => $this->_userPass)));
			
			$this->_userType = $loginResponse->LoginResult->UserDetails->UserType;
			$this->_availableSearches = $loginResponse->LoginResult->UserDetails->AvailableSearches;
			$this->_userIsValid = $loginResponse->LoginResult->UserDetails->UserIsValid;
			
			$this->_latestReturnCode = $loginResponse->LoginResult->ReturnCode->Code;
			$this->_latestDescription = $loginResponse->LoginResult->ReturnCode->Description;
			
			/* First checking if login is successful? */
			if($this->_userIsValid){
				/* Lets get the token returning from webservice */
				$this->_userToken = $loginResponse->LoginResult->UserToken;
				return true;
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}
	
	/**
	 * 
	 * Use this method for a broad search of the RTS Credit database. With the exception of the session token, all or some of the parameters may be passed.
	 * 
	 * @param string $MCNumber Optional. 6 digit Motor Carrier Number, "Shipper","F/F", or "EXEMPT"
	 * @param string $name Optional. Full or partial name of broker
	 * @param string $city Optional. Full or partial city name
	 * @param string $state Optional. Any two character state abbreviation
	 * @param string $zip Optional. Any valid US zip code 
	 * 
	 * @return mixed array of brokers on success if more then one broker found, broker if only one broker found, boolean false on failure
	 * 
	 */
	public function BrokerSearch($MCNumber, $name, $city, $state, $zip) {
		try{
			if($this->_userIsValid){
				$brokerSearchResponse = $this->_soapClient->__soapCall("BrokerSearch", array("BrokerSearch" => array("UserToken" => $this->_userToken, "MCNumber" => $MCNumber, "Name" => $name, "City" => $city, "St" => $state, "Zip" => $zip)));
				
				$this->_latestReturnCode = $brokerSearchResponse->BrokerSearchResult->ReturnCode->Code;
				$this->_latestDescription = $brokerSearchResponse->BrokerSearchResult->ReturnCode->Description;
				
				if($this->_latestReturnCode == 200){
					if(is_array($brokerSearchResponse->BrokerSearchResult->BrokerList)){
						return $this->xml2array($brokerSearchResponse->BrokerSearchResult->BrokerList);
					}else{
						return $brokerSearchResponse->BrokerSearchResult->BrokerList->Broker;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}
	
	/**
	 * 
	 * Use this method for a search of the RTS Credit database by city and/or state.
	 * 
	 * @param string $city Optional. Full or partial city name
	 * @param string $state Optional. Any two character state abbreviation
	 * 
	 * @return mixed array of brokers on success if more then one broker found, broker if only one broker found, boolean false on failure
	 * 
	 */
	public function BrokerSearchByCityState($city, $state) {
		try{
			if($this->_userIsValid){
				$brokerSearchByCityResponse = $this->_soapClient->__soapCall("BrokerSearchByCityState", array("BrokerSearchByCityState" => array("UserToken" => $this->_userToken, "City" => $city, "St" => $state)));
				
				$this->_latestReturnCode = $brokerSearchByCityResponse->BrokerSearchByCityStateResult->ReturnCode->Code;
				$this->_latestDescription = $brokerSearchByCityResponse->BrokerSearchByCityStateResult->ReturnCode->Description;
				
				if($this->_latestReturnCode == 200){
					if(is_array($brokerSearchByCityResponse->BrokerSearchByCityStateResult->BrokerList)){
						return $this->xml2array($brokerSearchByCityResponse->BrokerSearchByCityStateResult->BrokerList);
					}else{
						return $brokerSearchByCityResponse->BrokerSearchByCityStateResult->BrokerList->Broker;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}
	
	/**
	 * 
	 * Use this method for a search of the RTS Credit database by MC Number.
	 * 
	 * @param string $MCNumber Optional. 6 digit Motor Carrier Number, "Shipper","F/F", or "EXEMPT"
	 * 
	 * @return mixed array of brokers on success if more then one broker found, broker if only one broker found, boolean false on failure
	 * 
	 */
	public function BrokerSearchByMC($MCNumber) {
		try{
			if($this->_userIsValid){
				$brokerSearchByMCResponse = $this->_soapClient->__soapCall("BrokerSearchByMC", array("BrokerSearchByMC" => array("UserToken" => $this->_userToken, "MCNumber" => $MCNumber)));
				
				$this->_latestReturnCode = $brokerSearchByMCResponse->BrokerSearchByMCResult->ReturnCode->Code;
				$this->_latestDescription = $brokerSearchByMCResponse->BrokerSearchByMCResult->ReturnCode->Description;
				
				if($this->_latestReturnCode == 200){
					if(is_array($brokerSearchByMCResponse->BrokerSearchByMCResult->BrokerList)){
						return $this->xml2array($brokerSearchByMCResponse->BrokerSearchByMCResult->BrokerList);
					}else{
						return $brokerSearchByMCResponse->BrokerSearchByMCResult->BrokerList->Broker;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}	
	
	/**
	 * 
	 * Use this method for a search of the RTS Credit database by broker name.
	 * 
	 * @param string $name Optional. Full or partial name of broker
	 * 
	 * @return mixed array of brokers on success if more then one broker found, broker if only one broker found, boolean false on failure
	 * 
	 */
	public function BrokerSearchByName($name) {
		try{
			if($this->_userIsValid){
				$brokerSearchByNameResponse = $this->_soapClient->__soapCall("BrokerSearchByName", array("BrokerSearchByName" => array("UserToken" => $this->_userToken, "Name" => $name)));
				
				$this->_latestReturnCode = $brokerSearchByNameResponse->BrokerSearchByNameResult->ReturnCode->Code;
				$this->_latestDescription = $brokerSearchByNameResponse->BrokerSearchByNameResult->ReturnCode->Description;
				
				if($this->_latestReturnCode == 200){
					if(is_array($brokerSearchByNameResponse->BrokerSearchByNameResult->BrokerList)){
						return $this->xml2array($brokerSearchByNameResponse->BrokerSearchByNameResult->BrokerList);
					}else{
						return $brokerSearchByNameResponse->BrokerSearchByNameResult->BrokerList->Broker;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}
	
	/**
	 * 
	 * Use this method for a search of the RTS Credit database by zipcode.
	 * 
	 * @param string $zip Optional. Any valid US zip code 
	 * 
	 * @return mixed array of brokers on success if more then one broker found, broker if only one broker found, boolean false on failure
	 * 
	 */
	public function BrokerSearchByZip($zip) {
		try{
			if($this->_userIsValid){
				$brokerSearchByZipResponse = $this->_soapClient->__soapCall("BrokerSearchByZip", array("BrokerSearchByZip" => array("UserToken" => $this->_userToken, "Zip" => $zip)));
				
				$this->_latestReturnCode = $brokerSearchByZipResponse->BrokerSearchByZipResult->ReturnCode->Code;
				$this->_latestDescription = $brokerSearchByZipResponse->BrokerSearchByZipResult->ReturnCode->Description;
				
				if($this->_latestReturnCode == 200){
					if(is_array($brokerSearchByZipResponse->BrokerSearchByZipResult->BrokerList)){
						return $this->xml2array($brokerSearchByZipResponse->BrokerSearchByZipResult->BrokerList);
					}else{
						return $brokerSearchByZipResponse->BrokerSearchByZipResult->BrokerList->Broker;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}	
	
	/**
	 * 
	 * Use this method to obtain a full credit report from the RTS CRedit database.
	 * 
	 * @param string $id Required. The value of the ID node returned from any of the search methods 
	 * 
	 * @return mixed array of brokers on success if more then one broker found, broker if only one broker found, boolean false on failure
	 * 
	 */
	public function GetBrokerDetail($id) {
		if(!$id){
			return false;
		}
		try{
			if($this->_userIsValid){
				$getBrokerDetailResponse = $this->_soapClient->__soapCall("GetBrokerDetail", array("GetBrokerDetail" => array("UserToken" => $this->_userToken, "ID" => $id)));
				
				$this->_latestReturnCode = $getBrokerDetailResponse->GetBrokerDetailResult->ReturnCode->Code;
				$this->_latestDescription = $getBrokerDetailResponse->GetBrokerDetailResult->ReturnCode->Description;
				
				if($this->_latestReturnCode == 200){
					return $getBrokerDetailResponse->GetBrokerDetailResult->BrokerDetail;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		catch(Exception $ex){
			echo $ex->getMessage();
			return false;
		}
	}	
		
	/**
	 * 
	 * convert XML result to array
	 *
	 * @param  string $xmlstr the XML string
	 * 
	 * @return array
	 * 
	 */
	private function xml2array($xmlstr) {
		$children = array();
		$i = 0;
		
		$xml = simplexml_load_string($xmlstr);
		foreach($xml->children() as $child){
			foreach($child->attributes() as $key => $val){
				$children[$i][$key] = (string) $val;
			}
			$i ++;
		}
		return $children;
	}
	
	/**
	 * 
	 * Returns user type
	 * 
	 * @return string
	 * 
	 */
	public function getUserType() {
		return $this->_userType;
	}
	
	/**
	 * 
	 * Returns user accounts available search 
	 * 
	 * @return integer
	 * 
	 */
	public function getAvailableSearches() {
		return $this->_availableSearches;
	}
	
	/**
	 * 
	 * Returns user account is valid or not 
	 * 
	 * @return boolean
	 * 
	 */
	public function getUserIsValid() {
		return $this->_userIsValid;
	}
	
	/**
	 * 
	 * Get latest return code from webservice call
	 * 
	 * @return string
	 * 
	 */
	public function getLatestReturnCode() {
		return $this->_latestReturnCode;
	}
	
	/**
	 * 
	 * Get latest description from webservice call
	 * 
	 * @return string
	 * 
	 */
	public function getLatestDescription() {
		return $this->_latestDescription;
	}
	
	/**
	 * 
	 * Returns the SOAP headers from the last request. 
	 * 
	 * @return string 
	 * 
	 */
	public function getLastRequetHeaders() {
		return $this->_soapClient
			->__getLastRequestHeaders();
	}
	
	/**
	 * 
	 * Returns the XML sent in the last SOAP request.
	 * 
	 * @return mixed XML string
	 * 
	 */
	public function getLastRequest() {
		return $this->_soapClient
			->__getLastRequest();
	}
	
	/**
	 * 
	 * Returns the SOAP headers from the last response. 
	 * 
	 * @return string 
	 * 
	 */
	public function getLastResponseHeaders() {
		return $this->_soapClient
			->__getLastResponseHeaders();
	}
	
	/**
	 * 
	 * Returns the XML sent in the last SOAP response.
	 * 
	 * @return mixed XML string
	 * 
	 */
	public function getLastResponse() {
		return $this->_soapClient
			->__getLastResponse();
	}
}

?>