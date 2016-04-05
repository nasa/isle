<?php
  namespace ISLE;
  
  class UIException extends \Exception
  {
    private $valErrors;
    
    public function __construct($message = NULL, $valErrors = NULL, $code = 0, \Exception $previous = NULL)
    {
      $this->valErrors = $valErrors;
      parent::__construct($message, $code, $previous);
    }
    
    public function getValErrors() {
      return $this->valErrors;
    }
    
  }
?>