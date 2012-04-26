<?php
/**
* This is a simple library for sending emails with Postmark.
* Created by Matthew Loberg (http://mloberg.com), extended and modified by Drew Johnston (http://drewjoh.com).
* also extende to work with Spaghetti Framework by Djalma Araújo (github.com/djalmaaraujo)
*/

class Postmark {

	private $api_key;
	private $attachment_count = 0;
	private $data = array();

	function __construct($key, $from, $reply = '')
	{
		$this->api_key = $key;
		$this->data['From'] = $from;
		$this->data['ReplyTo'] = $reply;
	}


	function send()
	{
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'X-Postmark-Server-Token: '.$this->api_key
		);
		
		$ch = curl_init('https://api.postmarkapp.com/email');
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
		$return = curl_exec($ch);
		$curl_error = curl_error($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		// do some checking to make sure it sent
		if($http_code !== 200)
			return false;
		else
			return true;
	}


	function sendBatch($to = array()){
		$headers = array(
			"Accept: application/json",
			"Content-Type: application/json",
			"X-Postmark-Server-Token: {$this->api_key}"
		);
		$data = array();
		
		if (!empty($to)) {
			$data[] = $this->data;
			foreach($to as $email) {
				$this->data['to'] = $email;
				$data[] = $this->data;
			}
			
		}
		
		$ch = curl_init('https://api.postmarkapp.com/email/batch');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$return = curl_exec($ch);
		$curl_error = curl_error($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		// do some checking to make sure it sent
		if($http_code !== 200){
			var_dump($return);
			return false;
		}else{
			return true;
		}
	}


	function to($to)
	{
		$this->data['To'] = $to;
		return $this;
	}
	
	
	function cc($cc)
	{
		$this->data["Cc"] = $cc;
		return $this;
	}
	
	
	function bcc($bcc)
	{
		$this->data["Bcc"] = $bcc;
		return $this;
	}
		
	
	function subject($subject)
	{
		$this->data['Subject'] = $subject;
		return $this;
	}


	function html_message($html)
	{
		$this->data['HtmlBody'] = $html;
		return $this;
	}


	function plain_message($msg)
	{
		$this->data['TextBody'] = $msg;
		return $this;
	}


	function tag($tag)
	{
		$this->data['Tag'] = $tag;
		return $this;
	}
	
	
	function attachment($name, $content, $content_type)
	{
		$this->data['Attachments'][$this->attachment_count]['Name']		= $name;
		$this->data['Attachments'][$this->attachment_count]['ContentType']	= $content_type;
		
		// Check if our content is already base64 encoded or not
		if( ! base64_decode($content, true))
			$this->data['Attachments'][$this->attachment_count]['Content']	= base64_encode($content);
		else
			$this->data['Attachments'][$this->attachment_count]['Content']	= $content;
		
		// Up our attachment counter
		$this->attachment_count++;
		
		return $this;
	}
	
	
	function viewData($data) {
		$this->viewData = $data;
		return $this;
	}
	
	
	function load_view($path)
	{
		$view = new View();
		$view->data = $this->viewData;
		$content = $view->render($path, $this->layout);
		$this->data['HtmlBody'] = $content;
		return $this;
	}
}