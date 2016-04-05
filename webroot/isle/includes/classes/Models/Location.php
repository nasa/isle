<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class Location extends Node
  {
    const TABLE_NAME = 'locations';
    
    const CENTER_LENGTH = 255;
    const BLDG_LENGTH = 255;
    const ROOM_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["center"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateCenter', 'label' => 'Center');
      $props["bldg"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateBuilding', 'label' => 'Building');
      $props["room"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateRoom', 'label' => 'Room');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      return array();
    }
	
    /* Static validation methods */
    
    static public function validateCenter($center)
    {
      return Validate::stringLength($center, self::CENTER_LENGTH, 'Location Center');
    }
    
    static public function validateBuilding($bldg)
    {
      return Validate::stringLength($bldg, self::BLDG_LENGTH, 'Location Building');
    }
    
    static public function validateRoom($room)
    {
      return Validate::stringLength($room, self::ROOM_LENGTH, 'Location Room');
    }
  }
?>