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
    
    //open the three log files and store in variables.
    $errorLog = file_get_contents($logLocation . 'error.log');
    $errorLog =  $errorLog ? $errorLog : '';
    $errorLog1 = file_get_contents($logLocation . 'error.log.1');
    $errorLog1 =  $errorLog1 ? $errorLog1 : '';
    $securityLog = file_get_contents($logLocation . 'security.log');
    $securityLog =  $securityLog ? $securityLog : '';
    $securityLog1 = file_get_contents($logLocation . 'security.log.1');
    $securityLog1 =  $securityLog1 ? $securityLog1 : '';
    $fourofourLog = file_get_contents($logLocation . '404.log');
    $fourofourLog =  $fourofourLog ? $fourofourLog : '';
    $fourofourLog1 = file_get_contents($logLocation . '404.log.1');
    $fourofourLog1 =  $fourofourLog1 ? $fourofourLog1 : '';
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() . " - Logs</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list_ie7.css') . '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list.css') . '" /><!--<![endif]-->';
    $tmpl_headcontent .= '<link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'views/versions.css') . '"/>';
    
    $tmpl_javascripts = $tmpl_javascripts_main;
    
    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'logs.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
