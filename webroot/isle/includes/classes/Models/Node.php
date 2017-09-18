<?php
  namespace ISLE\Models;
  
  use ISLE\Exception;
  use ISLE\UIException;
  use ISLE\Validate;
  
  abstract class Node implements INode
  {   
    const ID_MAX = PHP_INT_MAX;
    const ID_MIN = 1;
    
    const NEW_ID = 0; // Should be outside of [ID_MIN..ID_MAX]
    
    static private $_reservedTypes = array('bool', 'boolean', 'decimal', 'double',
                                           'float', 'int', 'integer', 'string');
    
    public function __toString()
    {
      return $this->id;
    }
    
    /**
     * Only return defined properties
     *
     * @throws Exception if property does not exist
     */
    public function __get($name)
    {
      if (!in_array($name, array_keys(static::getProperties())))
      {
        throw new Exception('Invalid '.basename(get_class($this)).' property "'.
                            htmlspecialchars($name).'".', Exception::MODEL_INTEGRITY);
      }
      return property_exists($this, $name) ? $this->$name : NULL;
    }
    
    /**
     * Only allows defined properties to be set (not the default PHP action)
     *
     * @throws Exception if property does not exist
     */
    public function __set($name, $value)
    {
      if (!in_array($name, array_keys(static::getProperties())))
      {
        throw new Exception('Invalid '.basename(get_class($this)).' property "'.
                            htmlspecialchars($name).'".', Exception::MODEL_INTEGRITY);
      }
      $this->$name = $value;
    }
    
    /**
     * Returns defined properties.  This should be called by getProperties in the extended class.
     *
     * @return array Associative array of properties and their type, required status, and validator method
     */
    static protected function _getProperties()
    {
      $props = array();
      $props["id"] = array('type' => 'int', 'required' => TRUE,
                           'validator' => 'validateId');
      return $props;
    }

    /**
     * Creates a Node object from the HTTP Request variable
     *
     * @param string $prefix Key prefixed to the key associated with the model property
     */
    static public function prepare($prefix = '')
    {
        $n = new self();
        foreach (array_keys(self::getProperties()) as $prop)
        {
          $n->$prop = trim($_REQUEST[$prefix.$prop]);
        }
      return $n;
    }
    
    static public function reservedTypes()
    {
      return self::$_reservedTypes;
    }
    
    /**
     * Validates the Node.
     *
     * @return Node Returns object for chaining
     */
    public function validate()
    {
      $valErrors = array();
      
        foreach (static::getProperties() as $prop => $a)
        {
          if (is_null($this->$prop) or
              (is_string($this->$prop) and strlen($this->$prop) == 0))
          {
            if ($a["required"])
            {
              $valErrors[$prop] = $a['label'] . ' is required.';
            }
          }
          else {
            switch ($a["type"])
            {
              case 'bool':
              case 'boolean':
              case 'decimal':
              case 'double':
              case 'float':
              case 'int':
              case 'integer':
              case 'string':
                try {
                  static::$a["validator"]($this->$prop);
                }
                catch (UIException $e) {
                  // create a key value array where the key is the property in error and the value is the message.
                  $valErrors[$prop] = $e->getMessage();
                }
                break;
              default: // These should be ISLE\Models
                try {
                  call_user_func(__NAMESPACE__.'\\'.$a["type"].'::'.$a["validator"], $this->$prop);
                }
                catch (UIException $e) {
                // create a key value array where the key is the property in error and the value is the message.
                  $valErrors[$prop] = $e->getMessage();
                }
            }
          }
        }
        //check to see if there were any validation failures.
        //if so then thrown an exception and include the error messages.
        if(!empty($valErrors)) {
          throw new UIException('One or more errors occurred', $valErrors);
        }
    }
    
    /* Static validation methods */
    
    /**
     * Validates an id
     *
     * @param int $id Id to be validated
     * @throws Exception if id is invalid
     * @returns int Validated id
     */
    static public function validateId($id)
    {
      if ($id == self::NEW_ID)
      {
        return self::NEW_ID;
      }

      return Validate::integerRange($id, self::ID_MIN, self::ID_MAX,
                                    basename(get_called_class()).' id');
    }
  }
?>
