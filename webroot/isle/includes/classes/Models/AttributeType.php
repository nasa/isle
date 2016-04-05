<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class AttributeType extends Node
  {
    const TABLE_NAME = 'attribute_types';
    
    const ABBR_LENGTH = 255;
    const UNIT_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["unit"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateUnit', 'label' => 'Unit');
      $props["abbr"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateAbbreviation', 'label' => 'Abbreviation');
      $props["parent"] = array('type' => 'AttributeType', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Parent');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      return array();
    }
	
    /* Static validation methods */
    
    static public function validateAbbreviation($abbr)
    {
      return Validate::stringLength($abbr, self::ABBR_LENGTH, 'Attribute Type Abbreviation');
    }
    
    static public function validateUnit($unit)
    {
      return Validate::stringLength($unit, self::UNIT_LENGTH, 'Attribute Type Unit');
    }
  }
?>