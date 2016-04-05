<?php
    require_once 'includes/config.php';
    
    if(isset($_GET['action'])) {
      throw new ISLE\Exception('Actions are not valid on this page.', ISLE\Exception::FOUROFOUR);
    }
    
    if($u['role'] < ISLE\Models\Role::ADMIN) {
      $_SESSION['message']['type'] = 'alert-error';
      $_SESSION['message']['text'] = 'You do not have permission to do that.';
      header("Location: " . $rootdir . "assets");
      exit;
    }
    
    //setup an array or structure that contains the field names. so you only have one place to update if you want to change them
    $fieldNames['id'] = "id";
    $fieldNames['uid'] = "selUID";
    $fieldNames['name'] = "hidName";
    $fieldNames['email'] = "hidEmail";
    $fieldNames['role'] = "selRole";
    
    // config-todo: set $employees to an array of employees.
    $employees = null;
    
    $modelClass = new ISLE\Models\Role();
    $order[0]['col'] = 'id';
    //get list of roles from db, so ui can load into dropdown.
    $roles = $svc->getAll($modelClass, null, null, null, null, null, $order);
    
    $tmpl_headcontent = "<title>" . ISLE\Service::getInstanceName() . " - Users</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list_ie7.css') . '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list.css') . '" /><!--<![endif]-->';
    
    $tmpl_javascripts = $tmpl_javascripts_main;
    
    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'users.php';
    require_once $layoutsPath . 'pageend.php'; 
?>