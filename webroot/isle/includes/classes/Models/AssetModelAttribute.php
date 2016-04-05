<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class AssetModelAttribute extends Node
  {
    const TABLE_NAME = 'asset_model_attributes';

    const VALUE_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["model"] = array('type' => 'AssetModel', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Model');
      $props["attribute"] = array('type' => 'Attribute', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Attribute');
      $props["value"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateValue', 'label' => 'Value');
      return $props;
    }
    
    static public function getForeignKeys()
    {
      $props["attribute"] = array('type' => 'Attribute');
      
      return $props;
    }   
    
    /* Static validation methods */
    
    static public function validateValue($value)
    {
      return Validate::stringLength($value, self::VALUE_LENGTH, 'Asset Model Attribute Value');
    }
  }
?>