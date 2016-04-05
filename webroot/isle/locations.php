<?php
    require_once 'includes/config.php';
    
    if(isset($_GET['action'])) {
      throw new ISLE\Exception('Actions are not valid on this page.', ISLE\Exception::FOUROFOUR);
    }
    //setup an array or structure that contains the field names. so you only have one place to update if you want to change them
    
    $fieldNames['locationForm']['id'] = "hidId";
    $fieldNames['locationForm']['center'] = "selCenter";
    $fieldNames['locationForm']['bldg'] = "txtBldg";
    $fieldNames['locationForm']['room'] = "txtRoom";
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() . " - Locations</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list_ie7.css') . '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list.css') . '" /><!--<![endif]-->';
    
    $tmpl_javascripts = $tmpl_javascripts_main;

    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'locations.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
