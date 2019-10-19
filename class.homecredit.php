<?php
class HomeCredit
{    
  var $URL_TEST = "https://apicz-test.homecredit.net/verdun-train/";
  var $URL_PROD = "https://api.homecredit.cz/";
    
  var $test  = FALSE;   // if test mode
  
  var $debug = array(); // storing CURL events
   
  var $TOKEN = array(); 
  var $RESULT = FALSE;

  /**
   *  INIT
   *  
   *  - test mode as default
   *  
   */
    
  function __construct($TestMode = TRUE)
  {
    $this->test = $TestMode === TRUE;
    
    if ($this->test)
    {
      // -- empty params set test auth 
      
      $this->auth();
    }
  }
  
  /**
   *  Return production || test URL
   *  
   *  @return (string) depends on (bool) $this->test
   *  
   */
    
  function getApiUrl()
  {    
    return ($this->test) ? $this->URL_TEST : $this->URL_PROD; 
  }

  /**
   *  Get Auth Info for CURL header
   *  
   *  @return associative (array)
   */
   

  function getLogin()
  {
    return $login = array("username" => $this->user, 
                          "password" => $this->pass);
  }

  /**
   *  SET AUTH
   *  
   *  @params: (string) HC auth 
   *
   *  - empty CALL for test mode
   *  - auto turn off test mode when auth
   */
    
  function auth($shop = "024242", $username = "024242tech", $password = "024242tech")
  {
    $this->shop = $shop;
    $this->user = $username;
    $this->pass = $password;
    
    $this->test = $username == '024242tech';
  }

  /**
   *  Local CURL
   *  
   *  @param:  (string) $url
   *  @param:   (array) $post_data
   *  
   *  @set:     (array) $debug  // events log
   *  
   *  @return:  (array) API RESULT
   *  
   */
      
  function curl($url, $post_data = array())
  {            
    $header = array('Content-Type: application/json', 'Charset: utf-8');
    
    // -- append auth to header
    
    $token = $this->getToken(); 
    
    if ($token)
    {
      $header[] =  'Authorization: Bearer ' . $token; 
    }
            
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL,$url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_POST, true);    
        
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post_data));
      
    $result = curl_exec($ch);
    
    if (!curl_errno($ch)) 
    {
      switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) 
      {      
        case 200:  
      
          $this->debug[] = $url . ' => HTTP code : ' . $http_code . ' = OK';
      
        break;
      
        default: 
        
          $this->debug[] = $url . 'Unexpected HTTP code: ' . $http_code;
      }  
    }     
   
    $this->debug[] = curl_getinfo($ch);

    curl_close($ch);
    
    return json_decode($result,TRUE);        
  }
  
  /**
   *  TEST CONNECTION
   *  
   *  just by existing response
   *  
   *  @return: (bool)
   */
  
  function isHealthy()
  {
    $url = $this->getApiUrl() . "financing/v1/health";
        
    $result = $this->curl($url, $this->getLogin());
    
    return (is_array($result) && isset($result["error_description"]));        
  }
  
  /**
   *  STORE API TOKEN         
   *
   *  @set:   (array) $this->TOKEN  | uset $this->getToken()
   *  
   *  @return: (bool) when success
   *  
   */
      
  function setToken()
  {                 
    $this->TOKEN = array(); 
            
    $url = $this->getApiUrl() . "authentication/v1/partner/";       
     
    $result = $this->curl($url, $this->getLogin());
                    
    if (is_array($result) && isset($result["accessToken"]))
    {
      $this->TOKEN = $result;
      
      $this->TOKEN["time"] = time();          
    } 
    else
    {
      $this->debug[] = $result;
    }
     
    return isset($this->TOKEN["accessToken"]);                 
  }
  
  /**
   *  GET CURRENT API TOKEN
   *  
   *  @return (string) token || (bool) when token is not set
   *  
   */
  
  function getToken()
  {                
    if (!is_array($this->TOKEN) || !isset($this->TOKEN["accessToken"]))
    { 
      return FALSE;
    }  
                                    
    return ($this->TOKEN["expiresIn"] > time() - $this->TOKEN["time"] + 30) ? $this->TOKEN["accessToken"] : FALSE; 
  }
  
  /**
   *  vygenerování žádosti "createApplication"
   *  
   *  set @RESULT as response
   *  
   *  return (bool) success
   *  
   */
   
   function createApplication($json = array())
   {
     $url = $this->getApiUrl() . 'financing/v1/applications';       
     
     $this->RESULT = $this->curl($url, $json);
     
     return $this->isApplication();                             
   }
   
   /**
    *  Check if $this->createApplication was successful
    *  
    */
   
   function isApplication()
   {            
      return (is_array($this->RESULT) && isset($this->RESULT["gatewayRedirectUrl"]));
   }

   /**
    *  Return link for redirect
    *  
    */
   
   function getLink()
   {
     if (!$this->isApplication())
     {
       return FALSE;
     }
     else
     {
       return $this->RESULT["gatewayRedirectUrl"];
     }          
   }         
}
