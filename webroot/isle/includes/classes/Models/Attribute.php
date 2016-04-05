<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class Attribute extends Node
  {
    const TABLE_NAME = 'attributes';
    
    const NAME_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["name"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateName', 'label' => 'Name');
      $props["type"] = array('type' => 'AttributeType', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Type');
      return $props;
    }
    
    static public function getForeignKeys()
    {   
      $props["type"] = array('type' => 'AttributeType');
      
      return $props;
    }
    
    /* Static validation methods */
    
    static public function validateName($name)
    {
      return Validate::stringLength($name, self::NAME_LENGTH, 'Attribute Name');
    }
  }
?>