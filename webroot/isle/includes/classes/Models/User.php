<?php
  namespace ISLE\Models;
  
  use ISLE\Validate;
  
  class User extends Node
  {
    const TABLE_NAME = 'users';
    
    static public function getProperties()
    {
      $propTypes = parent::_getProperties();
      $propTypes["uid"] = array('type' => 'int', 'required' => TRUE,
                                'validator' => 'validateUID', 'label' => 'User');
      $propTypes["name"] = array('type' => 'string', 'required' => TRUE,
                                 'validator' => 'validateName', 'label' => 'Name');
      $propTypes["email"] = array('type' => 'string', 'required' => FALSE,
                                  'validator' => 'validateEmail', 'label' => 'Email');
      $propTypes["role"] = array('type' => 'Role', 'required' => TRUE,
                                 'validator' => 'validateId', 'label' => 'Role');
      return $propTypes;
    }
    
    static public function getForeignKeys()
    {      
      $props["role"] = array('type' => 'Role');
      
      return $props;
    }
    
    /* Static validation methods */
    
    static public function validateUID($uid)
    {
      return $uid;
    }
    
    static public function validateName($name)
    {
      return $name;
    }
    
    static public function validateEmail($email)
    {
      return Validate::email($email);
    }
  }
?>
