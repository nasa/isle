<?php
  namespace ISLE\Models;
  
  use ISLE\UIException;
  use ISLE\Validate;
  
  class AssetModelAttachment extends Node
  {
    const TABLE_NAME = 'asset_model_attachments';
    
    const NAME_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["name"] = array('type' => 'string', 'required' => TRUE,
                             'validator' => 'validateName', 'label' => 'Name');
      $props["model"] = array('type' => 'AssetModel', 'required' => TRUE,
                              'validator' => 'validateId', 'label' => 'Model');
      $props["num"] = array('type' => 'int', 'required' => TRUE,
                            'validator' => 'validateInt', 'label' => 'Num');
      $props["extension"] = array('type' => 'string', 'required' => TRUE,
                                  'validator' => 'validateExt', 'label' => 'Extension');
      return $props;
    }
    
    static public function getForeignKeys()
    {
      return array();
    }
    
    static public function validateName($name)
    {
      return Validate::stringLength($name, self::NAME_LENGTH, 'Name');
    }
    
    static public function validateInt($value)
    {
      return Validate::integerRange($value, parent::ID_MIN, parent::ID_MAX, 'Num');
    }
    
    static public function validateExt($value)
    {
      if($value == 'pdf') {
        return $value;
      }
      else {
        throw new UIException('Attachment must be a pdf.');
      }
    }
  }
?>
