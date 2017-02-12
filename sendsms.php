#!/usr/bin/php
<?php
  class VoipmsApi {
    private $BASE_URL = 'https://voip.ms/api/v1/rest.php';
    private $usernameEnc;
    private $passwordEnc;
    
    public function __construct($api_username, $api_password) {
      $this->usernameEnc = urlencode($api_username);
      $this->passwordEnc = urlencode($api_password);
    }
    
    private function jsonApiCall($methodName, $paramArray) {
      $url = $this->BASE_URL;
      
      $url .= "?method=$methodName&api_username=$this->usernameEnc&api_password=$this->passwordEnc";
      
      if(!empty($paramArray)) {
        for($i = 0; $i < count($paramArray); $i += 2) {
          $url .= '&' . $paramArray[$i] . '=' . urlencode($paramArray[$i+1]);
        }
      }
      
      $ret = file_get_contents($url);
      return json_decode($ret);
    }
  public function getDIDsInfo() {
    return $this->jsonApiCall('getDIDsInfo', null);
  }
  public function getSMS($did) {
    // Voip.Ms now only holds past 3 months
    $from = date("Y-m-d", strtotime("-3 months"));
    //$type = '1';
    $limit = '30';
    
    return  $this->jsonApiCall('getSMS', array('did', $did, 'from', $from, /*'type', $type,*/ 'limit', $limit));
  }
  public function sendSMS($did, $to, $message) {
    return  $this->jsonApiCall('sendSMS', array('did', $did, 'dst', $to, 'message', $message));
  }
  
  public function sendMessageFirstDid($to, $message) {
    $did = $this->getFirstSmsDid();
    $result = $this->sendSMS($did, $to, $message);
    
    if($result->status == 'success')
      return true;
    else
      return $result->status;
  }
    
  public function getFirstSmsDid() {
		 $dids = $this->getDIDsInfo();
		 
		 foreach($dids->dids as $did) {
			 if($did->sms_available == '1' && $did->sms_enabled == '1')
				 return $did->did;
		 }
		 
		 return null;
	 }
  public function validCredentials() {
    $result = $this->getDIDsInfo();
    
    if($result->status == 'success')
      return true;
    else if($result->status == 'invalid_credentials')
      return false;
    else
      return $result->status;
  }
	 
	 public function getMessagesFirstDid() {
	   $did = $this->getFirstSmsDid();
	   $result = $this->getSMS($did);
	   
	   if($result->status == 'success') {
  	   $messages = array();
	     
	     foreach($result->sms as $msg) {
	       $newmsg = new stdClass();
	       $newmsg->received = ($msg->type == '1');
	       $newmsg->contact = $msg->contact;
	       $newmsg->content = $msg->message;
	       $newmsg->date = $msg->date;
	       array_push($messages, $newmsg);
	     }
	     
	     return $messages;
	   }
	   return null;
	 }
	 
	 public function getMessagesTreeFirstDid() {
	   $did = $this->getFirstSmsDid();
	   $result = $this->getSMS($did);
	   
	   if($result->status == 'success') {
  	   $messages = array();
  	   
  	   foreach($result->sms as $msg) {
  	     if(empty($messages[$msg->contact]))
  	        $messages[$msg->contact] = array();
	       $newmsg = new stdClass();  	        
  	     $newmsg->received = ($msg->type == '1');
	       $newmsg->content = $msg->message;
	       $newmsg->date = $msg->date;
	       
  	     array_push($messages[$msg->contact], $newmsg);
  	   }
  	   
  	   return $messages;
	   }
	   
	   return null;
	 }
  }
 
$username=$argv[1];
$password=$argv[2];

$from_did=$argv[3];
$to_did=$argv[4];
$message=$argv[5];

$api = new VoipmsApi($username, $password);
$dids = $api->getDIDsInfo();

$api->sendSMS($from_did,$to_did,$message);

?>