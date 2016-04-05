<?php
  require_once 'includes/config.php';

  if(($_GET['item'] == 'model' || $_GET['item'] == 'asset') && intval($_GET['value']) > 0 && intval($_GET['num']) > 0 && $_GET['extension'] == 'pdf' && preg_match('/^\w+$/', $_GET['name'])) {
    
    $filenameshort = intval($_GET['value']) . '_' . intval($_GET['num']) . '.' . $_GET['extension'];
    if($_GET['item'] == 'model') {
      $filename = ISLE\Service::getUploadPath() . '/docs/assetmodels/' . $filenameshort;
    }
    else {
      $filename = ISLE\Service::getUploadPath() . '/docs/assets/' . $filenameshort;
    }

    //Send a Content-Type header:
    header('Content-Type: application/pdf');

    // Send a Content-Disposition header:
    header('Content-Disposition:inline; filename="' . $_GET['name'] . '.' . $_GET['extension'] . '"');

    // Send a Content-Length header:
    $fs = filesize($filename);
    header ("Content-Length:$fs\n");

    // Send the actual file:
    readfile($filename);
  }
?>