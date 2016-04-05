<?php
  /**
   * The ISLE\Service class provides data services to the isle web application.
   */
  namespace ISLE;
  
  use ISLE\Models\Node as Node;
  
  class Service
  {
    /**
	  * @var ISLE\DataProviders\ISLE data provider
	  */
    protected $dp;
    
    public function __construct()
    {
      $this->dp = new DataProviders\ISLE();
    }

    static public function getInstanceName()
    {
      $path = $_SERVER["REQUEST_URI"];
      $instance = substr($path, (strlen(SERVER_WEBROOT) ? strpos($path, SERVER_WEBROOT) : 0) + strlen(SERVER_WEBROOT));
      $instance = trim($instance, "/");
      $instance = substr($instance, 0, strpos($instance, "/"));
      return $instance;
    }
    
    static public function getUploadPath()
    {
      return realpath(__DIR__ . '/../../../' . self::getInstanceName() . '/uploads');
    }
	
    /* Generic Node methods */
    
	  /**
     * Selected method mapping for undeclared methods, where [Node] is a ISLE\model
     * UNDECLARED METHOD                              DECLARED METHOD
     * -------------------------------------------    -----------------------------
     * add[Node](Node $n)                          -> add(Node $n)
     * delete[Node](int $id)                       -> delete(Node $n)
     * find[Node]([Node $n [, array $opts = NULL]) -> find(Node $n, [$opts = NULL)
     * getAll[Node]s([array $opts = NULL])         -> find(Node $n, [$opts = NULL])
     * get[Node](int $id)                          -> get(Node $n)
     * update[Node](Node $n)                       -> update(Node $n)
     */
    
    public function __call($name, $args) {
      if (substr($name, 0, 3) == 'add') {
        return $this->add($args[0]);
      }
      if (substr($name, 0, 6) == 'delete') {
        $class = __NAMESPACE__.'\\Models\\'.substr($name, 6);
        $n = new $class();
        $n->id = $args[0];
        return $this->delete($n);
      }
      if (substr($name, 0, 4) == 'find') {
        return call_user_func_array($this->find, $args);
      }
      if (substr($name, 0, 3) == 'getAll') {
        $class = __NAMESPACE__.'\\Models\\'.substr($name, 6, -1);
        $n = new $class();
        return $this->find($n, $args[0]);
      }
      if (substr($name, 0, 3) == 'get') {
        $class = __NAMESPACE__.'\\Models\\'.substr($name, 3);
        $n = new $class();
        $n->id = $args[0];
        return $this->get($n);
      }
      if (substr($name, 0, 6) == 'update') {
        return $this->update($args[0]);
      }
      throw new Exception('Unknown service method "'.htmlspecialchars($name).'".');
    }
    
    /**
     * Adds a Node to the data provider
     *
     * @param Node $n Node to be added populated with required properties
     * @return The Node populated with the new id in addition to the original properties
     */
    public function add(Node $n)
    {
      $class = get_class($n);
      $n->id = $class::NEW_ID;
      return $this->_save($n);
    }
    
    /**
     * Deletes a node from the data provider
     *
     * @param Node $n Node to be deleted populated with its id or unique properties
     * @throws Exception if Node does not exist or on data provider exception
     * @return bool TRUE on success
     */
    public function delete(Node $n)
    {
      try
      {
       $n = $this->get($n);
       return $this->dp->delete($n);
      }
      catch (\Exception $e)
      {
        throw new Exception('Delete failed:'.$e->getMessage(), $e->getCode(), $e);
      }
    }
    
    public function deleteAll(Node $n, $filter = null) {
      try
      {
        $ret = $this->dp->deleteAll($n, $filter);
      }
      catch (\Exception $e)
      {
        throw new Exception('DeleteAll failed:'.$e->getMessage(), $e->getCode(), $e);
      }

      return $ret;
    }
    
    /**
     * Finds Nodes with matching properties
     *
     * @param Node $n Node populated with properties to match.  NULL values are wildcards.
     * @param array $opts Sort and limit options for the returned array
     * @return array Matching Nodes in the data provider
     */
    public function find(Node $n, $opts = NULL)
    {
      $n = $n->clean();
      $class = get_class($n);
      foreach ($class::getProperties() as $prop => $a)
      {
        if (!is_null($n->$prop))
        {
          if (in_array($a["type"], $class::reservedTypes()))
          {
            $n->$prop = $class::$a["validator"];
          } else {
            // Property is a model reference, look up by id
            $f = 'get'.$a["type"];
            $n->$prop = $this->$f($n->$prop)->id;
          }
        }
      }
      $opts = self::checkOptions($opts);
      try
      {
        return $this->dp->find($n, $opts);
      }
      catch (\Exception $e)
      {
        throw new Exception('Find failed for '.basename($class), Exception::SERVICE_DP, $e);
      }
    }
    /**
     * Gets a Node from the data provider
     * 
     * @param Node $n Node populated with its id or unique properties
     * @throws Exception if Nodes does not exist or on a data provider exception
     * @returns Node populated with all properties from the data provider
     */
    public function get(Node $n)
    {
      $class = get_class($n);
      if (isset($n->id))
      {
        $id = $class::validateId($n->id);
        $n = new $class();
        $n->id = $id;
      } else {
        $n->id = $class::NEW_ID;
        $n->validate();
        $n->id = NULL;
      }
      try
      {
        $ret = $this->dp->get($n);
      }
      catch (\Exception $e)
      {
        throw new Exception('Get failed for '.basename($class), Exception::SERVICE_DP, $e);
      }
      if ($ret === FALSE)
      {
        throw new Exception(basename($class).' id "'.$n->id.'" does not exist.', Exception::SERVICE_DNE);
      }
      return $ret;
    }
    
    public function getAll(Node $n, $start = 0, $limit = PHP_INT_MAX, $select = null, $distinct = false, $filter = null, $order = null) {
      $class = get_class($n);
      try
      {
        $start = ($start == null) ? 0 : $start;
        $limit = ($limit == null) ? PHP_INT_MAX : $limit;
        $ret = $this->dp->getAll($n, $start, $limit, $select, $distinct, $filter, $order);
      }
      catch (\Exception $e)
      {
        throw new Exception('GetAll failed for '.basename($class), Exception::SERVICE_DP, $e);
      }

      return $ret;
    }
    
    public function getAllAssets(Node $n, $start = 0, $limit = PHP_INT_MAX, $select = null, $distinct = false, $filter = null, $order = null) {
      try
      {
        $ret = $this->dp->getAllAssets($n, $start, $limit, $select, $distinct, $filter, $order);
      }
      catch (\Exception $e)
      {
        throw new Exception('GetAllAssets failed for '.basename($class), Exception::SERVICE_DP, $e);
      }

      return $ret;
    }
    
    public function count(Node $n, $filter = null) {
      $class = get_class($n);
      try
      {
        $ret = $this->dp->count($n, $filter);
      }
      catch (\Exception $e)
      {
        throw new Exception('Count failed for '.basename($class), Exception::SERVICE_DP, $e);
      }

      return $ret;
    }
    
    public function countAssets(Node $n, $filter = null) {
      $class = get_class($n);
      try
      {
        $ret = $this->dp->countAssets($n, $filter);
      }
      catch (\Exception $e)
      {
        throw new Exception('CountAssets failed for '.basename($class), Exception::SERVICE_DP, $e);
      }

      return $ret;
    }
    
    public function getForeignKeyReferences( Node $n ) {
      return $this->dp->getForeignKeyReferences($n);
    }
    
    public function getReport($report)
    {
      return $this->dp->getReport($report);
    }
    
    /**
     * Updates an existing Nodes properties in the data provider
     *
     * @param Node $n Node to be updated populated with the required properties
     * @return bool TRUE on success
     */
    public function update(Node $n)
    {
      $class = get_class($n);
      $n2 = new $class();
      $n2->id = $n->id;
      $this->get($n2);
      return $this->_save($n);
    }

    /**
     * Performs validation for the add and update methods, then saves to the data provider
     *
     * @param Node $n Node to be validated and saved
     * @return mixed See add and update methods for respective return values
     */
    protected function _save(Node $n)
    {
      $n->validate();
      $class = get_class($n);
      foreach ($class::getProperties() as $prop => $a)
      {
        if (!is_null($n->$prop))
        {
          if (!in_array($a["type"], $class::reservedTypes()))
          {
            // Property is a model reference, look up by id
            $f = 'get'.$a["type"];
            $n->$prop = $this->$f($n->$prop)->id;
          }
        }
      }
      if ($n->id == $class::NEW_ID)
      {
        $n->id = NULL;
        try
        {
          return $this->dp->add($n);
        }
        catch (\Exception $e)
        {
          throw new Exception('Add failed for '.basename($class), Exception::SERVICE_DP, $e);
        }
      } else {
        try
        {
          return $this->dp->update($n);
        }
        catch (\Exception $e)
        {
          throw new Exception('Update failed for '.basename($class), Exception::SERVICE_DP, $e);
        }
      }
    }

    /* Helper methods */
    
    static protected function checkOptions(Node $n, $opts)
    {
      if (!is_null($opts))
      {
        if (!is_array($opts))
        {
          throw new Exception('Options must be an associative array.');
        }
        if (isset($opts["sort"]))
        {
          foreach ($opts["sort"] as $prop => $order)
          {
            if (!in_array($order, DataProvider::sortDirs()))
            {
              throw new Exception('Invalid sort direction "'.htmlspecialchars($order).'".');
            }
            if (!property_exists($n, $prop))
            {
              throw new Exception('Property "'.htmlspecialchars($prop).'" does not exist in class "'.get_class($n).'".');
            }
          }
        }
      } else {
        $opts = array('sort' => array('id' => 'ASC')); // Default options
      }
      return $opts;
    }
  }
?>