<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class AssetModelRelation extends Node
  {
    const TABLE_NAME = 'asset_model_relations';
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["source"] = array('type' => 'AssetModel', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Source');
      $props["relation"] = array('type' => 'Relation', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Relation');
      $props["target"] = array('type' => 'AssetModel', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Target');
      return $props;
    }
    
    static public function getForeignKeys()
    { 
      $props["source"] = array('type' => 'AssetModel');
      $props["relation"] = array('type' => 'Relation');
      $props["target"] = array('type' => 'AssetModel');
      
      return $props;
    }
  }
?>