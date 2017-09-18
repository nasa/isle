<?php
  use ISLE\Secrets;

  namespace ISLE;
  
  class Validate
  {
    static public function integerRange($val, $min, $max, $label = 'Value')
    {
      $valOrig = $val;
      if (($val = filter_var($val, FILTER_VALIDATE_INT, array('options' => array('min_range' => $min, 'max_range' => $max)))) === FALSE)
      {
        throw new Exception($label.' "'.htmlspecialchars($valOrig).'" must be an integer between '.$min.' and '.$max);
      }
      return $val;
    }
    
    static public function stringLength($str, $len, $label = 'String')
    {
      if (strlen($str) > $len)
      {
        throw new UIException($label.' must be no more than '.$len.' characters.');
      }
      return $str;
    }
    
    static public function stringRange($str, $minim, $maxim, $label = 'String')
    {
      if(is_string($str) and is_int($minim) and is_int($maxim)) {
        if(strlen($str) >= $minim and strlen($str) <= $maxim) {
          return $str;
        }
        else {
          throw new UIException($label . ' must be ' . $minim . '-' . $maxim . ' characters long.');
        }
      }
      else {
        throw new Exception('Validate->stringRange: Invalid input parameters');
      }
    }
    
    static public function url($url, $checkSafe = true)
    {
	  if (($urlFiltered = filter_var($url, FILTER_VALIDATE_URL)) === FALSE)
	  {
	    throw new UIException('Invalid URL.');
	  }
    if($checkSafe) {
      // config-todo: add your API key.
      $httpResponse = Validate::http_request('GET','sb.google.com',80,'/safebrowsing/api/lookup',array('client' => 'api', 'apikey' => 'ADD_YOUR_KEY_HERE', 'appver' => '1.0', 'pver' => '3.0', 'url' => $urlFiltered), array(), NULL, array(), array(), array(), 5, false, true);
      $httpStatus = substr($httpResponse, 0, strpos($httpResponse, "\r\n"));
      if(strpos($httpStatus, "200") !== false) {
        //malicious or phishing site.
        $httpBody = substr($httpResponse, strpos($httpResponse, "\r\n\r\n") + 4);
        switch($httpBody){
          case 'malware':
            throw new UIException('This URL may contain malware, therefore it will not be allowed. <a href="http://code.google.com/apis/safebrowsing/safebrowsing_faq.html#whyAdvisory" target="_blank">Advisory provided by Google</a>');
            break;
          case 'phishing':
            throw new UIException('This URL may be a phishing site, therefore it will not be allowed. <a href="http://code.google.com/apis/safebrowsing/safebrowsing_faq.html#whyAdvisory" target="_blank">Advisory provided by Google</a>');
            break;
          case 'phishing,malware':
            throw new UIException('This URL may contain malware and be a phishing site, therefore it will not be allowed. <a href="http://code.google.com/apis/safebrowsing/safebrowsing_faq.html#whyAdvisory">Advisory provided by Google</a>');
            break;
        }
      }
      if(strpos($httpStatus, "204") === false) {
        //an error occured.
        throw new Exception('Error submitting url to Google Safe Browsing API in url function of Validate.php: ' . $httpStatus);
      }
    }
    
	  return $urlFiltered;
    }
    
    static public function date($date)
    {
      if(preg_match('/^(0?[1-9]|1[012])[- \/.]?(0?[1-9]|[12][0-9]|3[01])[- \/.]?((19|20)\d\d)$/',$date, $mdy) == 0) {
        throw new UIException('Invalid date.');
      }  
      
      try
      {
        $t = \DateTime::createFromFormat('m/d/Y',
                                         $mdy[1] . "/" . $mdy[2] . "/" . $mdy[3],
                                         new \DateTimeZone(Secrets::TIME_ZONE));
        return $t->format('Y-m-d');
      }
      catch (\Exception $e)
      {
        throw new UIException('Invalid date.');
      }
    }
    
    static public function dateRange($date, $min = NULL, $max = NULL)
    {
      if(preg_match('/^(0?[1-9]|1[012])[- \/.]?(0?[1-9]|[12][0-9]|3[01])[- \/.]?((19|20)\d\d)$/',$date, $mdy) == 0) {
        throw new UIException('Invalid date.');
      }
      
      if(is_null($min)) {
        $min = \DateTime::createFromFormat('m/d/Y', "01/02/1900",
                                           new \DateTimeZone(Secrets::TIME_ZONE));
      }
      
      if(is_null($max)) {
        $max = \DateTime::createFromFormat('m/d/Y', date('m/d/Y'),
                                           new \DateTimeZone(Secrets::TIME_ZONE));
      }
      
      try
      {
        $t = \DateTime::createFromFormat('m/d/Y',
                                         $mdy[1] . "/" . $mdy[2] . "/" . $mdy[3],
                                         new \DateTimeZone(Secrets::TIME_ZONE));
      }
      catch (\Exception $e)
      {
        throw new UIException('Invalid date.');
      }
      
      $interval = $t->diff($min);
      //intval of 0 or less means date is >= min.
      if($interval->format('%r%a') > 0) {
        throw new UIException('Date cannot be before ' . $min->format('m/d/Y') . '.');
      }
      
      $interval = $t->diff($max);
      //intval of 0 or more means date is <= max.
      if($interval->format('%r%a') < 0) {
        throw new UIException('Date cannot be after ' . $max->format('m/d/Y') . '.');
      }
      
      return $t->format('Y-m-d');
    }
    
    static public function email($email)
    {
      if(preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+\.[a-zA-Z]{2,6}$/",$email) == 0) {
        throw new UIException('Invalid email.');
      }
      return $email;
    }
    
    static public function http_request( 
        $verb = 'GET',             /* HTTP Request Method (GET, POST, and DELETE supported) */ 
        $ip,                       /* Target IP/Hostname */ 
        $port = 80,                /* Target TCP port */ 
        $uri = '/',                /* Target URI */ 
        $getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
        $postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */
        $xmldata = NULL,
        $formdata = array(),
        $cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
        $custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */ 
        $timeout = 1,           /* Socket timeout in seconds */ 
        $req_hdr = false,          /* Include HTTP request headers */ 
        $res_hdr = false           /* Include HTTP response headers */ 
        ) 
    {
      $ret = '';
      $verb = strtoupper($verb); 
      $cookie_str = ''; 
      $getdata_str = count($getdata) ? '?' : ''; 
      $postdata_str = '';
      $boundary = "AaB03x";

      foreach ($getdata as $k => $v) 
                  $getdata_str .= urlencode($k) .'='. urlencode($v) . '&'; 

      foreach ($postdata as $k => $v) 
          $postdata_str .= urlencode($k) .'='. urlencode($v) .'&'; 

      foreach ($cookie as $k => $v) 
          $cookie_str .= urlencode($k) .'='. urlencode($v) .'; '; 

      $crlf = "\r\n";
      $req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf;
      $req .= 'Host: '. $ip . $crlf; 
      $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf; 
      $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf; 
      $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf; 
      $req .= 'Accept-Encoding: gzip,deflate' . $crlf; 
      $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;
      $req .= 'Connection: close' . $crlf;

      foreach ($custom_headers as $k => $v)
          $req .= $k .': '. $v . $crlf; 

      if (!empty($cookie_str)) 
          $req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf; 
      
      if ($verb == 'POST' && !empty($postdata_str)) 
      { 
          $postdata_str = substr($postdata_str, 0, -1); 
          $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf; 
          $req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf; 
          $req .= $postdata_str; 
      }
      else if ($verb == 'POST' && !empty($xmldata))
      {
          $req .= 'Content-type: application/xml' . $crlf;
          $req .= 'Content-Length: '. strlen($xmldata) . $crlf . $crlf; 
          $req .= $xmldata;
      }
      else if ($verb == 'POST' && !empty($formdata))
      {
        $req .= 'Content-Type: multipart/form-data; boundary=' . $boundary . $crlf;
        $reqTmp = '--'.$boundary.$crlf;
        foreach($formdata as $key => $value) {
          if($key === 'formVal') {
            $reqTmp .= $crlf;
          }
          $reqTmp .= $value.$crlf;
        }
        $reqTmp .= '--'.$boundary.'--';
        $req .= 'Content-Length: '. strlen($reqTmp) . $crlf . $crlf;
        $req .= $reqTmp;
      }
      else $req .= $crlf; 

      if ($req_hdr) 
          $ret .= $req; 
      
      $ssl = '';
      if($port == 443) {
        $ssl = 'tls://';
      }
      
      if (($fp = @fsockopen($ssl . $ip, $port, $errno, $errstr)) == false) 
          return "Error $errno: $errstr\n"; 

      stream_set_timeout($fp, $timeout); 

      fputs($fp, $req);
      while ($line = fgets($fp)) {
        $ret .= $line;
      }
      fclose($fp);

      //check the header to see if the response is gzipped then use gzdecode() to decode it.
      $httpHeader = substr($ret, 0, strpos($ret, "\r\n\r\n"));
      $httpHeader = Validate::parse_http_header($httpHeader);
      $httpHeader = array_change_key_case($httpHeader);
      
      if(array_key_exists(strtolower('Transfer-Encoding'), $httpHeader) && strtolower($httpHeader['transfer-encoding']) == 'chunked') {
        $chunked = true;
      }
      
      if(array_key_exists(strtolower('Content-Encoding'), $httpHeader) && strtolower($httpHeader['content-encoding']) == 'gzip') {
        $gzipped = true;
      }
      
      if (!$res_hdr)
          $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4);
      
      if(isset($chunked)) {
        $ret = Validate::unchunk_string($ret);
      }
      
      if(isset($gzipped)) {
        $ret = gzinflate(substr($ret,10,-8));
      }
      
      return $ret;
    }
    
    private function parse_http_header($str) {
      $lines = explode("\r\n", $str);
      $head  = array(array_shift($lines));
      foreach ($lines as $line) {
        list($key, $val) = explode(':', $line, 2);
        if ($key == 'Set-Cookie') {
          $head['Set-Cookie'][] = trim($val);
        } else {
          $head[$key] = trim($val);
        }
      }
      return $head;
    }
    
    private function unchunk_string ($str) {

      // A string to hold the result
      $result = '';

      // Split input by CRLF
      $parts = explode("\r\n", $str);

      // These vars track the current chunk
      $chunkLen = 0;
      $thisChunk = '';

      // Loop the data
      while (($part = array_shift($parts)) !== NULL) {
        if ($chunkLen) {
          // Add the data to the string
          // Don't forget, the data might contain a literal CRLF
          $thisChunk .= $part."\r\n";
          if (strlen($thisChunk) == $chunkLen) {
            // Chunk is complete
            $result .= $thisChunk;
            $chunkLen = 0;
            $thisChunk = '';
          } else if (strlen($thisChunk) == $chunkLen + 2) {
            // Chunk is complete, remove trailing CRLF
            $result .= substr($thisChunk, 0, -2);
            $chunkLen = 0;
            $thisChunk = '';
          } else if (strlen($thisChunk) > $chunkLen) {
            // Data is malformed
            return FALSE;
          }
        } else {
          // If we are not in a chunk, get length of the new one
          if ($part === '') continue;
          if (!$chunkLen = hexdec($part)) break;
        }
      }

      // Return the decoded data of FALSE if it is incomplete
      return ($chunkLen) ? FALSE : $result;

    }
  }
?>
