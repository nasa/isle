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
    $fieldNames['relationForm']['id'] = "hidId";
    $fieldNames['relationForm']['name'] = "txtName";
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() . " - Relations</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list_ie7.css') . '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list.css') . '" /><!--<![endif]-->';
    
    $tmpl_javascripts = $tmpl_javascripts_main;

    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'relations.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
