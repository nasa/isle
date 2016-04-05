<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class Role extends Node
  {
    const TABLE_NAME = 'roles';
    const NAME_LENGTH = 255;
    
    const DISABLED = 1;
    const VIEWER = 2;
    const USER = 4;
    const CONTRIBUTOR = 8;
    const ADMIN = 16;
	
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
      return Validate::stringLength($name, self::NAME_LENGTH, 'Role Name');
    }
  }
?>