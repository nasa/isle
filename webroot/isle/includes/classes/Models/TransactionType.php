<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class TransactionType extends Node
  {
    const TABLE_NAME = 'transaction_types';
  
    const NAME_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["name"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateName');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      return array();
    }
	
    /* Static validation methods */	
    static protected function validateName($name)
    {
      return Validate::stringLength($name, self::NAME_LENGTH, 'Transaction Type Name');
    }
  }
?>