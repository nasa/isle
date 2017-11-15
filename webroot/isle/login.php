<?php
    require_once 'includes/config.php';
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                        " - Login</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' .
                         auto_version($stylesPath . 'list_ie7.css') .
                         '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' .
                         auto_version($stylesPath . 'list.css') .
                         '" /><!--<![endif]-->';
    $tmpl_headcontent .= '<link rel="stylesheet" type="text/css" href="' .
                         auto_version($stylesPath . 'views/versions.css') . '"/>';
    
    $tmpl_javascripts = $tmpl_javascripts_main;
    
    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'login.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
