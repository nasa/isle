<?php
  namespace ISLE;
  
  class Exception extends \Exception
  {
    /* Codes */
    const UNKNOWN = 0; // An unknown error occurred.
    const SERVICE_DP = 301; // Data provider threw exception
    const SERVICE_DNE = 302; // Does not exist in the data provider
    const MODEL_INTEGRITY = 300; // Property does not exist in the model
    const MODEL_VALIDATION = 300; // Invalid value for model property
    const CSRF = 303; // A possible CSRF attack occurred.
    const AJAX = 304; // An exception was caught in remoteInterface.php while handling an AJAX request.
    const UPLOAD = 305; // An exception occurred as a result of a file upload.
    const FOUROFOUR = 404; // A bad url was entered.
    
    private $displayOutput;

    public function __construct($message = NULL, $code = self::UNKNOWN,
                                \Exception $previous = NULL, $displayOutput = true)
    {
      switch ($code)
      {
        case self::SERVICE_DP:
          $this->displayOutput = ($displayOutput === false ? false : true);
          parent::__construct($message.' DATABASE ERROR: "'.$previous->getMessage().'"');
          break;
        default:
          $this->displayOutput = ($displayOutput === false ? false : true);
          parent::__construct($message, $code, $previous);
      }
    }
    
    public function getDisplayOutput() {
      return $this->displayOutput;
    }
    public function displayOutputOn() {
      $this->displayOutput = true;
    }
    public function displayOutputOff() {
      $this->displayOutput = false;
    }
  }
?>
