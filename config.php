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

/*
 * Disable SOAP Caching
 */
ini_set('soap.wsdl_cache_enabled', '0');
ini_set('soap.wsdl_cache_ttl', '0'); 

/*
 * UserID and User password parameters:
 */
$userID = "user@email";
$userPass = "pass";

?>