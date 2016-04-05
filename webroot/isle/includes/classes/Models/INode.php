<?php
  namespace ISLE\Models;
  
  interface INode
  {
    /**
     * Returns defined properties.
     *
     * @return array Associative array of properties and their type, required status, and validator method
     */
	 static function getProperties();
  }
?>