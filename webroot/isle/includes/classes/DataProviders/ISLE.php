<?php
  /**
   * The ISLE\DataProviders\ISLE class provides ISLE data services to the ISLE\Services class.
   */
  namespace ISLE\DataProviders;
  
  use ISLE\Models\Node;
  use ISLE\Service;  

  use PDO;
  use PDOStatement;
  
  class ISLE
  {
      /**
     * @var object Data source
     */
    protected $ds;
      
      /**
       * @static array Array of sort by directions
       */
      static protected $sortDirs = array('ASC', 'DESC');
    
    public function __construct()
    {
      $this->ds = new \PDO('mysql:host:127.0.0.1;port=3306;dbname=isle_'.SERVER_INSTANCE.';charset=utf8', 'root','root');
      $this->ds->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
      $this->ds->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
	
    /* Static public methods */
    
    static public function sortDirs()
    {
      return self::$sortDirs;
    }

    static public function getTablePrefix()
    {
       return strtolower(Service::getInstanceName()) . "_";
    }
    
	/* Generic Node methods */
    
    public function add(Node $n)
    {
      $stmt = $this->ds->prepare(self::_add($n));
      self::bindValues($stmt, $n);
      $stmt->execute();
      $n->id = (int) $this->ds->lastInsertId();
      return $n;
    }
    
    public function delete(Node $n)
    {
        $stmt = $this->ds->prepare(self::_delete($n));
        self::bindValues($stmt, $n);
        return $stmt->execute();
    }
    
    public function deleteAll(Node $n, $filter) {
      $stmt = $this->ds->prepare(self::_deleteAll($n, $filter));
      if(is_array($filter['cols'])) {
        $class = get_class($n);
        $classProps = $class::getProperties();
        foreach($filter['cols'] as $obj) {
          if(isset($obj['col'], $obj['val'])) {
            if(array_key_exists($obj['col'], $classProps)) {
              $stmt->bindParam(':' . $obj['col'], $obj['val']);
            }
          }
        }
      }
      return $stmt->execute();
    }
    
    public function find(Node $n, $opts)
    {
      $class = get_class($n);
      $stmt = $this->ds->prepare(self::_find($n, $opts));
      self::bindValues($stmt, $n);
      $stmt->execute();
      self::bindColumns($stmt, $n);
      $ret = array();
      while ($stmt->fetch(PDO::FETCH_BOUND))
      {
        $ret[] = clone $n;
      }
      return $ret;
    }
    
    public function get(Node $n)
    {
      $stmt = $this->ds->prepare(self::_get($n));
      self::bindValues($stmt, $n);
      $stmt->execute();
      self::bindColumns($stmt, $n);
      return $stmt->fetch(PDO::FETCH_BOUND) ? $n : FALSE;
    }
    
    public function getAll(Node $n, $start, $limit, $select, $distinct, $filter, $order)
    {
      $stmt = $this->ds->prepare(self::_getAll($n, $start, $limit, $select, $distinct, $filter, $order));
      $stmt = self::_bindFilterParams($n, $stmt, $filter);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllAssets(Node $n, $start, $limit, $select, $distinct, $filter, $order)
    {
      $stmt = $this->ds->prepare(self::_getAllAssets($n, $start, $limit, $select, $distinct, $filter, $order));
      $stmt = self::_bindFilterParams($n, $stmt, $filter);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function count(Node $n, $filter)
    {
      $stmt = $this->ds->prepare(self::_count($n, $filter));
      $stmt = self::_bindFilterParams($n, $stmt, $filter);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function countAssets(Node $n, $filter)
    {
      $stmt = $this->ds->prepare(self::_countAssets($n, $filter));
      $stmt = self::_bindFilterParams($n, $stmt, $filter);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
    * getForeignKeyReferences
    *
    * returns true if the property 'id' in Node $n is used as foreign key in other tables and there are one or more entries in one or more of this foreign tables with this id in the respective foreign key column
    * 
    * @param Node $n     node name
    */
    public function getForeignKeyReferences( Node $n ) {
      $key = intval($n->id);
      $class = get_class($n);
      if($key > 0)
      {
        $result = $this->ds->query('SELECT `table_name`,`column_name` FROM `information_schema`.`key_column_usage` WHERE `referenced_table_name` = "'.self::getTablePrefix().$class::TABLE_NAME.'"');
        $select = array();
        while( $result && $row=$result->fetch(PDO::FETCH_ASSOC) )
        {
          array_push($select, '(SELECT COUNT(`'.$row['column_name'].'`) FROM `'.$row['table_name'].'` WHERE `'.$row['column_name'].'`="'.$key.'") AS `'.$row['table_name'].'`');
        }
        if(!empty($select))
        {
          $result2 = $this->ds->query("SELECT ".implode(',',$select));
          return $result2->fetch(PDO::FETCH_ASSOC);
        }
      }
      return false;
    }
    
    public function update(Node $n)
    {
      $stmt = $this->ds->prepare(self::_update($n));
      self::bindValues($stmt, $n);
      return $stmt->execute();
    }
    
    public function getReport($name)
    {
      switch ($name)
      {
        case 'computers':
          $q = '
SELECT
  `category` AS `Category`,
  COALESCE(`amos`.`os`, "N/A") AS `OS`,
  CONCAT(`l`.`center`, " B", `l`.`bldg`, "R", `l`.`room`) AS `Location`
FROM
( SELECT
    `id`,
    `model`
    `location`
  FROM `'.self::getTablePrefix().'assets`) AS `a`
JOIN
( SELECT
    `amc`.`model`,
    `c`.`name` AS `category`
  FROM `'.self::getTablePrefix().'asset_model_categories` AS `amc`
  JOIN `'.self::getTablePrefix().'categories` AS `c` ON `amc`.`category` = `c`.`id`
  WHERE `c`.`name` LIKE "%Computers"
) AS `cj` ON `a`.`model` = `cj`.`model`
JOIN `'.self::getTablePrefix().'locations` AS `l` ON `l`.`id` = `a`.`location`
LEFT OUTER JOIN
( SELECT
    `ama`.`model`,
    `ama`.`value` AS `os`
  FROM `'.self::getTablePrefix().'asset_model_attributes` as `ama`
  WHERE `attribute` = (SELECT `id` FROM `'.self::getTablePrefix().'attributes` WHERE `name` = "Operating System")
) AS `amos` ON `amos`.`model` = `a`.`model`';
          break;
        default:
          return array();
      }
      $stmt = $this->ds->query($q);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* mySQL generator methods */
    
    static protected function _add(Node $n)
    {
      $class = get_class($n);
      $props = self::_props($n);
      return 'INSERT INTO `'.self::getTablePrefix().$class::TABLE_NAME.'` (`'.implode('`,`', $props).'`) VALUES (:'.implode(',:', $props).')';
    }
    
    static protected function _delete(Node $n)
    {
      $class = get_class($n);
      return 'DELETE FROM `'.self::getTablePrefix().$class::TABLE_NAME.'` WHERE `id` = '. $n->id .' LIMIT 1';
    }
    
    static protected function _deleteAll(Node $n, $filter)
    {
      $class = get_class($n);
      $classProps = $class::getProperties();

      $where = '';
      $count = 0;
      
      if(is_array($filter['cols'])) {
        foreach($filter['cols'] as $obj) {
          $operator = '=';

          if(isset($obj['operator']) && strcasecmp($obj['operator'], 'LIKE') == 0) {
            $operator = 'LIKE';
          }

          if(isset($obj['col'], $obj['val']) && array_key_exists($obj['col'], $classProps)) {
            $count++;
            if($count == 1) {
              $where = ' WHERE ';
            }
            else {
              $where .= ' AND ';
            }
            $where .= '`'.self::getTablePrefix().$class::TABLE_NAME.'`.`'.$obj['col'].'` '.$operator.' :'.$obj['col'];
          }
        }
      }
      
      return 'DELETE FROM `'. self::getTablePrefix() . $class::TABLE_NAME . '`' . $where;
    }
    
    static protected function _find(Node $n, $opts)
    {
      return 'SELECT * FROM `'.self::getTablePrefix().$class::TABLE_NAME.'` WHERE '.(self::_filter($n) ?: '1').self::_options($opts);
    }
    
    static protected function _get(Node $n)
    {
      $class = get_class($n);
      return 'SELECT * FROM `'.self::getTablePrefix().$class::TABLE_NAME.'` WHERE '.self::_filter($n);
    }
    
    static protected function _getAll(Node $n, $start, $limit, $select, $distinct, $filter, $order)
    {
      $class = get_class($n);
      $classProps = $class::getProperties();
      
      $cols = '';
      
      foreach ($classProps as $prop => $a)
      {
        if(isset($select))
        {
          if(in_array($prop, $select))
          {
            if($distinct == TRUE)
            {
              $cols .= 'DISTINCT `'.self::getTablePrefix().$class::TABLE_NAME.'`.`'.$prop.'`, ';
              $distinct = FALSE;
            }
            else {
              $cols .= '`'.self::getTablePrefix().$class::TABLE_NAME.'`.`'.$prop.'`, ';
            }
          }
        }
        else {
          $cols .= '`'.self::getTablePrefix().$class::TABLE_NAME.'`.`'.$prop.'`, ';
        }
      }
      
      $joins = '';
      
      self::_buildJoins($class, $cols, $joins);
      
      $cols = substr_replace(trim($cols), "", -1);
      
      $where = self::_buildWhere($class, $classProps, $filter);
      $orderby = self::_buildOrderBy($class, $classProps, $order);
      
      return 'SELECT ' . $cols . ' FROM `'.self::getTablePrefix().$class::TABLE_NAME.'`'.$joins.$where.$orderby.' LIMIT '.$start.', '.$limit;
    }
    
    static protected function _getAllAssets(Node $n, $start, $limit, $select, $distinct, $filter, $order)
    {
      $class = get_class($n);
      $classProps = $class::getProperties();
      
      $cols = '';
      
      foreach ($classProps as $prop => $a) {
        $cols .= '`'.self::getTablePrefix().$class::TABLE_NAME.'`.`'.$prop.'`, ';
      }
      
      $joins = '';
      
      self::_buildJoins($class, $cols, $joins);
      
      if(is_array($filter[0]['cols']))
      {
        if ($filter[0]['cols'][0]['colClass'] == 'AssetModelCategory')
        {
          $joins .= '
LEFT OUTER JOIN `'.self::getTablePrefix().'asset_model_categories` ON `'.self::getTablePrefix().'asset_model_categories`.`model` = `'.self::getTablePrefix().'asset_models`.`id`';
        }
      }
      
      $cols .= '
  `LT`.`type` AS `Transaction_type`,
  `LT`.`finish` AS `Transaction_finish`,
  `LT`.`User_name` AS `Transaction_User_name`,
  `LT`.`User_email` AS `Transaction_User_email`,
  `LT`.`User_uid` AS `Transaction_User_uid`,
  `LT`.`location` AS `Transaction_location`';
      
      if(is_array($filter[0]['cols']) && $filter[0]['cols'][0]['colClass'] == 'Location')
      {
        $cols .= ',
`LT`.`Location_center` AS `Transaction_Location_center`,
`LT`.`Location_bldg` AS `Transaction_Location_bldg`,
`LT`.`Location_room` AS `Transaction_Location_room`';

        $joins .= '
LEFT OUTER JOIN
( SELECT
  `t1`.*,
  `U`.`name` AS `User_name`,
  `U`.`email` AS `User_email`,
  `U`.`uid` AS `User_uid`,
  `L`.`center` AS `Location_center`,
  `L`.`bldg` AS `Location_bldg`,
  `L`.`room` AS `Location_room`
FROM `'.self::getTablePrefix().'transactions` AS `t1`
JOIN `'.self::getTablePrefix().'users` AS `U` ON `t1`.`user` = `U`.`id` 
LEFT OUTER JOIN `'.self::getTablePrefix().'locations` AS `L` ON `t1`.`location` = `L`.`id`
LEFT OUTER JOIN `'.self::getTablePrefix().'transactions` AS `t2` ON (`t1`.`asset` = `t2`.`asset` AND `t1`.`time` < `t2`.`time`)
WHERE `t2`.`asset` IS NULL
) AS `LT` ON `'.self::getTablePrefix().'assets`.`id` = `LT`.`asset`';
      }
      else {
        $joins .= '
LEFT OUTER JOIN
( SELECT
    `t1`.*,
    `U`.`name` AS `User_name`,
    `U`.`email` AS `User_email`,
    `U`.`uid` AS `User_uid`
  FROM `'.self::getTablePrefix().'transactions` AS `t1`
  JOIN `'.self::getTablePrefix().'users` AS `U` ON `t1`.`user` = `U`.`id`
  LEFT OUTER JOIN `'.self::getTablePrefix().'transactions` AS `t2` ON (`t1`.`asset` = `t2`.`asset` AND `t1`.`time` < `t2`.`time`)
  WHERE `t2`.`asset` IS NULL
) AS `LT` ON `'.self::getTablePrefix().'assets`.`id` = `LT`.`asset`';
      }
      
      $where = self::_buildWhere($class, $classProps, $filter);
      $orderby = self::_buildOrderBy($class, $classProps, $order);
      
      return 'SELECT DISTINCT ' . $cols . ' FROM `'. self::getTablePrefix() . $class::TABLE_NAME . '`' . $joins . $where . $orderby . ' LIMIT ' . $start . ', ' . $limit;
    }
    
    static protected function _buildJoins($class, &$cols, &$joins, $parentNum = '')
    {
      $i = 1;
      $num = '';
      foreach ($class::getForeignKeys() as $prop => $a)
      {
        //prop should equal 'mfr' the column name of foreign key.
        // a['type'] equals the name of the class of the foreign key object.
        // a['type']::TABLE_NAME give the table name.
        $fkClass = 'ISLE\\Models\\' . $a['type'];

        foreach ($fkClass::getProperties() as $propb => $ab)
        {
          if($propb !== 'id') {
                if(strpos($cols, $a['type'].$num.'_'.$propb) !== FALSE)
                {
                  $i++;
                  $num = $i;
                }
                $cols .= '`'.self::getTablePrefix().$fkClass::TABLE_NAME.$num.'`.`'.$propb.'` "'.$a['type'].$num.'_'.$propb.'"'.', ';
          }
        }
        
        $as = '';
        if($num != '')
        {
          $as = ' AS `'.self::getTablePrefix().$fkClass::TABLE_NAME.$num.'`';
        }
        
        $joins .= '
LEFT OUTER JOIN `'.self::getTablePrefix().$fkClass::TABLE_NAME.'`'.$as.' ON `'.self::getTablePrefix().$class::TABLE_NAME.$parentNum.'`.`'.$prop.'` = `'.self::getTablePrefix().$fkClass::TABLE_NAME.$num.'`.`id`';
        self::_buildJoins($fkClass, $cols, $joins, $num);
      }
    }
    
    static protected function _count(Node $n, $filter)
    {
      $class = get_class($n);
      $classProps = $class::getProperties();
      
      $where = self::_buildWhere($class, $classProps, $filter);
      
      return 'SELECT COUNT(*) as `total` FROM `'.self::getTablePrefix().$class::TABLE_NAME.'`'.$where;
    }
    
    static protected function _countAssets(Node $n, $filter)
    {
      $class = get_class($n);
      $classProps = $class::getProperties();
      
      $joins = '';
      
      if(is_array($filter))
      {
        if(is_array($filter[0]['cols']))
        {
          if($filter[0]['cols'][0]['colClass'] == 'AssetModelCategory')
          {
            $joins .= ' LEFT JOIN `'.self::getTablePrefix().'asset_models` ON `'.self::getTablePrefix().'asset_models`.`id` = `'.self::getTablePrefix().'assets`.`model`';
            $joins .= ' LEFT JOIN `'.self::getTablePrefix().'asset_model_categories` ON `'.self::getTablePrefix().'asset_model_categories`.`model` = `'.self::getTablePrefix().'asset_models`.`id`';
          }
          else if($filter[0]['cols'][0]['colClass'] == 'AssetModel')
          {
            $joins .= ' LEFT JOIN `'.self::getTablePrefix().'asset_models` ON `'.self::getTablePrefix().'asset_models`.`id` = `'.self::getTablePrefix().'assets`.`model`';
          }
          else if($filter[0]['cols'][0]['colClass'] == 'Location')
          {
            $joins .= ' LEFT OUTER JOIN `'.self::getTablePrefix().'locations` ON `'.self::getTablePrefix().'assets`.`location` = `'.self::getTablePrefix().'locations`.`id`';
            
            $joins .= '
LEFT OUTER JOIN
( SELECT
    `t1`.*,
    `U`.`name` AS `User_name`,
    `U`.`email` AS `User_email`,
    `U`.`uid` AS `User_uid`,
    `L`.`center` AS `Location_center`,
    `L`.`bldg` AS `Location_bldg`,
    `L`.`room` AS `Location_room`
  FROM `'.self::getTablePrefix().'transactions` AS `t1`
  JOIN `'.self::getTablePrefix().'users` AS `U` ON `t1`.`user` = `U`.`id` 
  JOIN `'.self::getTablePrefix().'locations` AS `L` ON `t1`.`location` = `L`.`id`
  LEFT OUTER JOIN `'.self::getTablePrefix().'transactions` AS `t2` ON (`t1`.`asset` = `t2`.`asset` AND `t1`.`time` < `t2`.`time`)
  WHERE `t2`.`asset` IS NULL
) AS `LT` ON `'.self::getTablePrefix().'assets`.`id` = `LT`.`asset`';
          }
          else if($filter[0]['cols'][0]['colClass'] == 'User')
          {
            $joins .= '
LEFT OUTER JOIN
( SELECT
    `t1`.*,
    `U`.`name` AS `User_name`,
    `U`.`email` AS `User_email`,
    `U`.`uid` AS `User_uid`
  FROM `'.self::getTablePrefix().'transactions` AS `t1`
  JOIN `'.self::getTablePrefix().'users` AS `U` ON `t1`.`user` = `U`.`id`
  LEFT OUTER JOIN `'.self::getTablePrefix().'transactions` AS `t2` ON (`t1`.`asset` = `t2`.`asset` AND `t1`.`time` < `t2`.`time`)
  WHERE `t2`.`asset` IS NULL
) AS `LT` ON `'.self::getTablePrefix().'assets`.`id` = `LT`.`asset`';
          }
        }
        if(is_array($filter[1]['cols'])) {         
          if($joins == '' || (is_array($filter) && is_array($filter[0]['cols']) && ($filter[0]['cols'][0]['colClass'] == 'Location' || $filter[0]['cols'][0]['colClass'] == 'User'))) {
            $joins .= ' LEFT JOIN `'.self::getTablePrefix().'asset_models` ON `'.self::getTablePrefix().'asset_models`.`id` = `'.self::getTablePrefix().'assets`.`model`';
          }
          $joins .= ' LEFT JOIN `'.self::getTablePrefix().'manufacturers` ON `'.self::getTablePrefix().'manufacturers`.`id` = `'.self::getTablePrefix().'asset_models`.`mfr`';
        }
      }
      
      $where = self::_buildWhere($class, $classProps, $filter);
      
      return 'SELECT COUNT(DISTINCT `'.self::getTablePrefix().'assets`.`id`) AS `total` FROM `'.self::getTablePrefix().$class::TABLE_NAME.'`' . $joins . 
$where;
    }
    
    static protected function _update(Node $n)
    {
      $class = get_class($n);
      return 'UPDATE `'.self::getTablePrefix().$class::TABLE_NAME.'` SET '.implode(',', self::_buildProps2($n)).' WHERE `id` = :id';
    }
	
    /* MySQL generator helper methods */
    
    static protected function _bindFilterParams(Node $n, $stmt, $filter) {
      if(is_array($filter['cols'])) {
        self::_bindFilterParamsHelper($n, $stmt, $filter);
      }
      else {
        if(is_array($filter)) {
          foreach($filter as $ftr) {
            self::_bindFilterParamsHelper($n, $stmt, $ftr);
          }
        }
      }
      return $stmt;
    }
    
    static protected function _bindFilterParamsHelper(Node $n, &$stmt, $filter) {
      if(is_array($filter['cols'])) {
        $class = get_class($n);
        $classProps = $class::getProperties();
        $lph = '';
        $lphnum = 1;
        foreach($filter['cols'] as $obj) {
          if(isset($obj['col'], $obj['val'])) {
            $colClass = $class;
            $cp = $classProps;
            $classPrefix = '';
            if(isset($obj['colClass'])) {
              $colClass = "ISLE\Models\\" . $obj['colClass'];
              $cp = $colClass::getProperties();
              $classPrefix = $obj['colClass'] . '_';
            }

            $placeholder = $classPrefix . $obj['col'];
            if($placeholder == $lph) {
              $lph = $placeholder;
              $placeholder .= $lphnum;
              $lphnum++;
            }
            else {
              $lph = $placeholder;
            }

            if(array_key_exists($obj['col'], $cp)) {
              $stmt->bindParam(':' . $placeholder, $obj['val']);
            }
          }
        }
      }
    }
            
    static protected function _buildWhere($class, $classProps, $filter) {
      $where = '';
      $whereLT = '';
      $count = 0;
      $lph = '';
      $lphnum = 1;
      
      if(is_array($filter['cols'])) {
        foreach($filter['cols'] as $obj) {
          $operator = '=';

          if(isset($obj['operator']) && strcasecmp($obj['operator'], 'LIKE') == 0) {
            $operator = 'LIKE';
          }

          $cp = $classProps;
          $colClass = $class;
          $classPrefix = '';
          if(isset($obj['colClass'])) {
            $colClass = "ISLE\Models\\" . $obj['colClass'];
            $cp = $colClass::getProperties();
            $classPrefix = $obj['colClass'] . '_';
          }
          
          if(isset($obj['col'], $obj['val']) && array_key_exists($obj['col'], $cp)) {
            $count++;
            $sep = ' AND ';
            if(isset($filter['separator']) && $filter['separator'] == 'OR') {
              $sep = ' OR ';
            }
            
            if($count == 1) {
              $where = ' WHERE ';
            }
            else {
              $where .= $sep;
            }
            $placeholder = $classPrefix . $obj['col'];
            if($placeholder == $lph) {
              $lph = $placeholder;
              $placeholder .= $lphnum;
              $lphnum++;
            }
            else {
              $lph = $placeholder;
            }
            $where .= '`'.self::getTablePrefix().$colClass::TABLE_NAME.'`.`'. $obj['col'].'` '.$operator.' :'.$placeholder;
          }
        }
      }
      else {
        if(is_array($filter)) {
          foreach($filter as $ftr) {
            $count2 = 0;
            if($count > 0) {
              $where .= ' and (';
            }
            if(is_array($ftr['cols'])) {
              foreach($ftr['cols'] as $obj) {
                $operator = '=';

                if(isset($obj['operator']) && strcasecmp($obj['operator'], 'LIKE') == 0) {
                  $operator = 'LIKE';
                }

                $cp = $classProps;
                $colClass = $class;
                $classPrefix = '';
                if(isset($obj['colClass'])) {
                  $colClass = "ISLE\Models\\" . $obj['colClass'];
                  $cp = $colClass::getProperties();
                  $classPrefix = $obj['colClass'] . '_';
                }

                if(isset($obj['col'], $obj['val']) && array_key_exists($obj['col'], $cp)) {
                  $count++;
                  $count2++;
                  $sep = ' AND ';
                  if(isset($ftr['separator']) && $ftr['separator'] == 'OR') {
                    $sep = ' OR ';
                  }

                  if($count == 1) {
                    $where = ' WHERE (';
                    $whereLT = '';
                    if($obj['colClass'] == 'Location') {
                      $where = '(';
                      $whereLT = '(';
                    }
                  }
                  elseif($count2 > 1) {
                    $where .= $sep;
                    $whereLT .= $sep;
                  }

                  $placeholder = $classPrefix . $obj['col'];
                  if($placeholder == $lph) {
                    $lph = $placeholder;
                    $placeholder .= $lphnum;
                    $lphnum++;
                  }
                  else {
                    $lph = $placeholder;
                  }
                  $where .= '`'.self::getTablePrefix().$colClass::TABLE_NAME.'`.`'.$obj['col'].'` '.$operator.' :'.$placeholder;
                  $whereLT .= '`LT`.`'.$classPrefix.$obj['col'].'` '.$operator.' :'.$placeholder;
                }
              }
            }
            $where .= ')';
            if($obj['colClass'] == 'Location') {
              $whereLT .= ')';
            }
            if($obj['colClass'] == 'Location') {
              $where = ' WHERE ((`LT`.`location` IS NULL AND ' . $where . ')';
              $where .= ' OR (`LT`.`location` IS NOT NULL AND ' . $whereLT . '))';
            }
            else if($obj['colClass'] == 'User') {
              $where = ' WHERE (`LT`.`type` = 1 AND ' . $whereLT . ')';
            }
          }
        }
      }
      return $where;
    }
    
    static protected function _buildOrderBy($class, $classProps, $order) {
    
      $orderby = '';
      $count = 0;
      
      if(is_array($order)) {
        foreach($order as $obj) {
          
          if(isset($obj['colClass']) && $obj['colClass'] == 'Custom') {
            if(isset($obj['col']) && $obj['col'] == 'unique_id') {
              $count++;
              if($count == 1) {
                $orderby = ' ORDER BY ';
              }
              else {
                $orderby .= ', ';
              }
              
              $orderby .= '`'.self::getTablePrefix().'assets`.`serial`';
              
              if(isset($obj['dir']) && $obj['dir'] == 'DESC') {
                $orderby .=  ' DESC';
              }
              else {
                $orderby .= ' ASC';
              }
            }
          }
          else {
          
            $cp = $classProps;
            $colClass = $class;
            $classPrefix = '';
            if(isset($obj['colClass'])) {
              $colClass = "ISLE\Models\\" . $obj['colClass'];
              $cp = $colClass::getProperties();
              $classPrefix = $obj['colClass'] . '_';
            }

            if(isset($obj['col']) && array_key_exists($obj['col'], $cp)) {
              $count++;
              if($count == 1) {
                $orderby = ' ORDER BY ';
              }
              else {
                $orderby .= ', ';
              }


              if(isset($obj['dir']) && $obj['dir'] == 'DESC') {
                $orderby .= '`'.self::getTablePrefix().$colClass::TABLE_NAME.'`.`'.$obj['col'].'` DESC';
              }
              else {
                $orderby .= '`'.self::getTablePrefix().$colClass::TABLE_NAME.'`.`'.$obj['col'].'` ASC';
              }
            }
          }
        }
      }
      return $orderby;
    }
    
    
    static protected function _buildProps(Node $n)
    {
      $set = array();
      foreach (self::_props($n) as $prop)
      {
        $set[] = '`'.$prop.'` = :'.$prop;
      }
      return $set;
    }
    
    static protected function _buildProps2(Node $n)
    {
      $set = array();
      $class = get_class($n);
      $classProps = $class::getProperties();
      
      foreach ($classProps as $prop => $a)
      {
        if($n->$prop == null) {
          $set[] = '`'.$prop.'` = NULL';
        }
        else {
          $set[] = '`'.$prop.'` = :'.$prop;
        }
      }
      return $set;
    }
    
    static protected function _filter(Node $n)
    {
      return implode(' AND ', self::_buildProps($n));
    }
    
    static protected function _options($opts)
    {
      $sql = '';
      if (isset($opts["sort"]))
      {
        $sql .= ' ORDER BY ';
        foreach ($opts["sort"] as $key => $value)
        {
          $order[] = '`'.$key.'` '.(strtoupper($value) == 'DESC' ? 'DESC' : 'ASC');
        }
        $sql .= implode(',', $order);
      }
      if (isset($opts["limit"]))
      {
        $sql .= ' LIMIT '.((int) $opts["limit"]);
        if (isset($opts["page"]))
        {
          $offset = (int) (($opts["page"] - 1) * $opts["limit"]);
        }
      }
      if (isset($opts["offset"]))
      {
        $offset = (int) $opts["offset"];
      }
      if (isset($offset))
      {
        $sql .= ' OFFSET '.$offset;
      }
      return $sql;
    }

    static protected function _props(Node $n)
    {
      $props = get_object_vars($n);
      return array_diff(array_keys($props), array_keys($props, NULL, TRUE));
    }
    
    /* Helper methods */
    
    static protected function bindColumns(PDOStatement &$stmt, Node &$n)
    {
      $class = get_class($n);
      foreach ($class::getProperties() as $prop => $a)
      {
        $n->$prop = NULL;
        $stmt->bindColumn($prop, $n->$prop, self::pdoType($a["type"]));
      }
    }
    
    static protected function bindValues(PDOStatement &$stmt, Node &$n)
    {
      $class = get_class($n);
      if(is_array($class::getProperties())) {
        foreach ($class::getProperties() as $prop => $a)
        {
          if (isset($n->$prop))
          {
            if (!is_null($n->$prop))
            {
              $stmt->bindValue($prop, $n->$prop, self::pdoType($a["type"]));
            }
          }
        }
      }
    }
    
    static protected function pdoType($type)
    {
      switch ($type)
      {
         case 'bool':
         case 'boolean':
           return PDO::PARAM_BOOL;
         case 'decimal':
         case 'double':
         case 'float':
         case 'string':
           return PDO::PARAM_STR;
         case 'integer':
         case 'int':
         default: // Extended Node class type
           return PDO::PARAM_INT;
       }
    }
  }
?>
