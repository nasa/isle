<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  use ISLE\UIException;
  
  class TransactionCheckout extends Node
  {
    const TABLE_NAME = 'transactions';
    
    const PURPOSE_LENGTH = 255;
    const NOTES_LENGTH = 255;
	
    static public function getProperties()
    {
      $props = parent::_getProperties();
      $props["type"] = array('type' => 'TransactionType', 'required' => TRUE, 'validator' => 'validateId');
      $props["user"] = array('type' => 'User', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'User');
      $props["asset"] = array('type' => 'Asset', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Asset');
      $props["time"] = array('type' => 'string', 'required' => TRUE, 'validator' => 'validateTime', 'label' => 'Time');
      $props["location"] = array('type' => 'Location', 'required' => TRUE, 'validator' => 'validateId', 'label' => 'Location');
      $props["purpose"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validatePurpose', 'label' => 'Purpose');
      $props["finish"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateFinish', 'label' => 'Est. Finish Date');
      $props["notes"] = array('type' => 'string', 'required' => FALSE, 'validator' => 'validateNotes', 'label' => 'Notes');
      return $props;
    }
    
    static public function getForeignKeys()
    {
      $props["type"] = array('type' => 'TransactionType');
      $props["user"] = array('type' => 'User');
      $props["location"] = array('type' => 'Location');
      
      return $props;
    }
	
    /* Static validation methods */
    
    static protected function validateTime($time)
    {
      try
      {
        $t = new \DateTime($time, new \DateTimeZone('America/New_York'));
        return $t->format('Y-m-d H:i:s');
      }
      catch (\Exception $e)
      {
        throw new UIException('Invalid date.');
      }
    }
    
    static public function validateFinish(&$finish)
    {
      $finish = Validate::date($finish);
      return $finish;
    }
    
    static public function validatePurpose($purpose)
    {
      return Validate::stringRange($purpose, 1, self::PURPOSE_LENGTH, 'Purpose');
    }
    
    static public function validateNotes($notes)
    {
      return Validate::stringRange($notes, 1, self::NOTES_LENGTH, 'Notes');
    }
  }
?>