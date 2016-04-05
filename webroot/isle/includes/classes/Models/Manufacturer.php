<?php
  namespace ISLE\Models;

  use ISLE\Validate;

  class Manufacturer extends Node
  {
    const TABLE_NAME = 'manufacturers';

    const NAME_LENGTH = 255;
    const URL_LENGTH = 255;

    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["name"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateName', 'label' => 'Name');
      $props["url"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateURL', 'label' => 'URL');
      $props["parent"] = array('type' => 'Manufacturer', 'required' => FALSE, 'validator' => 'validateId');
      return $props;
    }
    
    static public function getForeignKeys()
    {      
      return array();
    }

    /* Static validation methods */
    
    static public function validateName($name)
    {
      return Validate::stringRange($name, 1, self::NAME_LENGTH, 'Manufacturer name');
    }
    
    static public function validateURL($url)
    {
      return Validate::url(\ISLE\Validate::stringRange($url, 1, self::URL_LENGTH, 'Manufacturer URL'));
    }
  }
?>