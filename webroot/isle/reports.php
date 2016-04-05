<?php
  require_once 'includes/config.php';
  
  $reportData = $svc->getReport($_GET["action"]);
  
  $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() . " - Reports</title>";
  $tmpl_headcontent .= $tmpl_headcontent_main;
  
  $tmpl_javascripts = $tmpl_javascripts_main;
  
  require_once $layoutsPath . 'pagestart.php';
  require_once $viewsPath . 'reports.php';
  require_once $layoutsPath . 'pageend.php'; 
?>
