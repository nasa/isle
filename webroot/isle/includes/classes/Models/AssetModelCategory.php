<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class AssetModelCategory extends Node
  {
    const TABLE_NAME = 'asset_model_categories';
  
    public $model;
    public $category;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["model"] = array('type' => 'AssetModel', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Model');
      $props["category"] = array('type' => 'Category', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Category');
      return $props;
    }
    
    static public function getForeignKeys()
    {
      $props["category"] = array('type' => 'Category');
      
      return $props;
    }
  }
?>