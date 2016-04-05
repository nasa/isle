<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class Relation extends Node
  {
    const TABLE_NAME = 'relations';
  
    const NAME_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["name"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateName', 'label' => 'Name');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      return array();
    }
	
    /* Static validation methods */
    
    static public function validateName($name)
    {
      return Validate::stringLength($name, self::NAME_LENGTH, 'Relation Name');
    }
  }
?>