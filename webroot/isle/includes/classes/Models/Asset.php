<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class Asset extends Node
  {
    const TABLE_NAME = 'assets';
    
    const SERIAL_LENGTH = 255;
    const NOTES_LENGTH = 255;
    
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["model"] = array('type' => 'AssetModel', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Model');
      $props["location"] = array('type' => 'Location', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Location');
      $props["serial"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateSerial', 'label' => 'Serial No.');
      $props["notes"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateNotes', 'label' => 'Notes');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      $props["model"] = array('type' => 'AssetModel');
      $props["location"] = array('type' => 'Location');
      
      return $props;
    }
	
    /* Static validation methods */
      
    static public function validateSerial($serial)
    {
      return Validate::stringLength($serial, self::SERIAL_LENGTH, 'Asset Serial Number');
    }
    
    static public function validateNotes($notes)
    {
      return Validate::stringLength($notes, self::NOTES_LENGTH, 'Asset Notes');
    }
  }
?>