<?php
    require_once 'includes/config.php';
    
    if(isset($_GET['action'])) {
      throw new ISLE\Exception('Actions are not valid on this page.', ISLE\Exception::FOUROFOUR);
    }
    
    if($u['role'] <= ISLE\Models\Role::USER) {
      $_SESSION['message']['type'] = 'alert-error';
      $_SESSION['message']['text'] = 'You do not have permission to do that.';
      header("Location: " . $rootdir . "assets");
      exit;
    }
    
    //setup an array or structure that contains the field names. so you only have one place to update if you want to change them
    $fieldNames['attributeForm']['id'] = "hidId";
    $fieldNames['attributeForm']['name'] = "txtName";
    $fieldNames['attributeForm']['type'] = "selType";
    
    $modelClass = new ISLE\Models\AttributeType();
    $order[0]['col'] = 'unit';
    //get list of attribute types from db, so ui can load into dropdown.
    $types = $svc->getAll($modelClass, null, null, null, null, null, $order);

    $items = array();
    $parents = array();
    foreach($types as $item) {
      $items[$item['id']] = $item;
      $parents[$item['parent']][] = $item['id'];
    }
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() . " - Attributes</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list_ie7.css') . '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list.css') . '" /><!--<![endif]-->';
    
    $tmpl_javascripts = $tmpl_javascripts_main;

    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'attributes.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
