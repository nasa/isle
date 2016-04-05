<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class Category extends Node
  {
    const TABLE_NAME = 'categories';
    
    const NAME_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["name"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateName', 'label' => 'Name');
      $props["parent"] = array('type' => 'Category', 'required' => FALSE, 'validator' => 'validateId');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      return array();
    }
	
    /* Static validation methods */
    
    static public function validateName($name)
    {
      return Validate::stringLength($name, self::NAME_LENGTH, 'Category Name');
    }
  }
?>