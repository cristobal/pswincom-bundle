<?php

/**
 * PSWinCom Bundle
 * 
 * A PHP library with simplistic approach for parsing/writing data from/to 
 * Microsoft Excel XML/CSV format
 *  
 * Copyright (c) 2011-2012 Cristobal <cristobal@dabed.org>
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
 * @author      Cristobal Dabed
 * @copyright   2012-2013 (c) Cristobal
 * @license     http://www.opensource.org/licenses/mit-license
 * @link        http://github.com/cristobal/pswincom-bundle
 * @package     PSWinCom
 * @version     0.1
 */

// TODO: Add support for tariff & servicecode
// TODO: Add support for add sms
namespace PSWinCom;

/**
 * PSWinCom
 *
 * @author Cristobal
 * @package PSWinCom
 */
class PSWinCom
{

	/**
	 * @private
	 */
	private $options;

	/**
	 * PSWinCom
	 */
	public function __construct($options = array()) {
		$defaults = array(
			'api_host' => "http://sms.pswin.com/sms",
			'debug_mode' => false,
			'username' => '',
			'password' => '',
			'sender'   => 'PSWinCom',
			'country_code' => '',
			'timeout' => 30
		);

		$options = array_merge($defaults, $options);
		if (empty($options['username']) || empty($options['password'])) {
			throw new Exception("username and/or password must be set", 1);	
		}

		$this->options = $options;
	}

	/**
	 * Send sms
	 *
	 * @param string $phone 
	 * @param string $message
	 * @param mixed  $options to override ()
	 */
	public function sendSms($phone, $message, $options = array()) {

		$global_options = $this->options;

		// Adjust phone number
		if (strlen($phone) == '8' && !empty($global_options['country_code'])) {
			$phone = sprintf('%s%s', $global_options['country_code'], $phone);
		}

		// Set sender
		$sender = $global_options['sender'];
		if (isset($options['sender']) && !empty($options['sender'])) {
			$sender = $options['sender'];
		}

		if ($global_options['debug_mode']) {
			return true;
		}


		/* Build xml */
		$data = array();
		$data['username'] = $global_options['username'];
		$data['password'] = $global_options['password'];
		$data['id'] 	  = 1; // TODO: See other options
		$data['message']  = $message;
		$data['phone']	  = $phone;
		$data['sender']   = $sender;

		$xml     = $this->buildXml($data);

		/* Create curl request  */
		$url     = $global_options['api_host'];
		$timeout = $global_options['timeout'];

		$ch = curl_init($url); 
			 
		curl_setopt	($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt ($ch, CURLOPT_MAXREDIRS, 10);

	    // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	    curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		// curl_setopt ($ch, CURLOPT_POST, count($params));
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml);       

		$content_type   = "text/xml";
		$content_length = strlen($xml);
		
		error_log($xml);

		$headers[] = sprintf('Content-type: %s', $content_type);                                              
		$headers[] = sprintf('Content-length: %d', $content_length);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
		
		// send request		
		$contents = curl_exec($ch);
		curl_close($ch);

		if (empty($contents)) {
			return false;
		}

		$contents = str_replace(array("\r", "\n"), "", strtolower($contents));
		$needle   = "<status>ok</status>";
		return (strpos($contents, $needle) !== FALSE);
	}

	/**
	 * Build xml
	 *
	 * @param mixed $data
	 */
	private function buildXml($data) {
		$contents = sprintf('<?xml version="1.0"?>' . "\n" . '<SESSION><CLIENT>%s</CLIENT><PW>%s</PW><MSGLST><MSG><ID>%d</ID><TEXT>%s</TEXT><RCV>%s</RCV><SND>%s</SND></MSG></MSGLST></SESSION>', $data['username'], $data['password'], $data['id'], $data['message'], $data['phone'], $data['sender']);
		return $contents;
	}
}

