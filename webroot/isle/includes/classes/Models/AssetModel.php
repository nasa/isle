<?php
  namespace ISLE\Models;
  
  use ISLE\Secrets;
  use ISLE\UIException;
  use ISLE\Validate;
  
  class AssetModel extends Node
  {
    const TABLE_NAME = 'asset_models';
	
    const MODEL_LENGTH = 255;
    const DESC_LENGTH = 255;
    const SERIES_LENGTH = 255;
    const URL_LENGTH = 255;
    
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["mfr"] = array('type' => 'Manufacturer', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Manufacturer');
      $props["model"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateModel', 'label' => 'Model');
      $props["desc"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateDescription', 'label' => 'Description');
      $props["series"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateSeries', 'label' => 'Series');
      $props["url"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateURL', 'label' => 'URL');
      $props["img"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateImage', 'label' => 'Image');
      $props["img_modified"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateImageModified', 'label' => 'Image Modified');
      return $props;
    }
    
    static public function getForeignKeys()
    {
      $props["mfr"] = array('type' => 'Manufacturer');
      
      return $props;
    }
	
    /* Static validation methods */
      
    static public function validateModel($model)
    {
      return Validate::stringLength($model, self::MODEL_LENGTH, 'Asset Model Number');
    }
    
    static public function validateDescription($desc)
    {
      return Validate::stringLength($desc, self::DESC_LENGTH, 'Description');
    }
    
    static public function validateSeries($series)
    {
      return Validate::stringLength($series, self::SERIES_LENGTH, 'Asset Model Series');
    }
    
    static public function validateURL($url)
    {
      return Validate::url(Validate::stringLength($url, self::URL_LENGTH, 'Asset Model URL'));
    }
    
    static public function validateImage($image)
    {
      if($image == 'jpg' || $image == 'gif' || $image == 'png') {
        return $image;
      }
      else {
        throw new UIException('Image must be gif, jpg, or png.');
      }
    }
    
    static protected function validateImageModified($time)
    {
      try
      {
        $t = new \DateTime($time, new \DateTimeZone(Secrets::TIME_ZONE));
        return $t->format('Y-m-d H:i:s');
      }
      catch (\Exception $e)
      {
        throw new UIException('Invalid image modified.');
      }
    }
  }
?>
