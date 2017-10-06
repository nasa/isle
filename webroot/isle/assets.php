<?php
    require_once 'includes/config.php';

    $action = '';

    if(isset($_GET['action'])) {
      $action = $_GET['action'];
      if(preg_match('/^[0-9]+$/', $action)) {
        $itemToEdit = $action;
        $action = 'edit';
      }
    }

    if($action != 'new' && $action != 'edit' && $action != '') {
      throw new ISLE\Exception('The requested asset action is invalid.',
                               ISLE\Exception::FOUROFOUR);
    }

    // If the referer field indicates that it came from the asset list, save the
    // query string in session.
    // If referrer ends with "assets" or if it contains "assets?"
    if(isset($_SERVER['HTTP_REFERER'])) {
      if(preg_match('/assets$/', $_SERVER['HTTP_REFERER']) ||
         preg_match('/assets\?/', $_SERVER['HTTP_REFERER'])) {
        $querystring = (strpos($_SERVER['HTTP_REFERER'], "?") ?
                        substr($_SERVER['HTTP_REFERER'],
                               strpos($_SERVER['HTTP_REFERER'], "?")) : '');

        if(!preg_match('/^\?(item|value|search|page)=.*(&(item|value|search|page)=.*)*$/',
                       $querystring)) {
          $querystring = '';
        }
        $_SESSION['querystring'] = $querystring;
      }
    }

    if(!isset($_SESSION['fromModel'])) {
      unset($_SESSION['addedModel']);
      unset($_SESSION['POST']);
    } else {
      unset($_SESSION['fromModel']);
    }


    if($action == 'new' && $u['role'] <= ISLE\Models\Role::USER) {
      $_SESSION['message']['type'] = 'alert-error';
      $_SESSION['message']['text'] = 'You do not have permission to do that.';
      header("Location: " . $rootdir . "assets");
      exit();
    }

    // Setup an array or structure that contains the field names. so you only
    // have one place to update if you want to change them.
    
    $fieldNames['checkoutForm']['asset'] = "hidAssetIdCO";
    $fieldNames['checkoutForm']['location'] = "selLocationCO";
    $fieldNames['checkoutForm']['purpose'] = "txtPurposeCO";
    $fieldNames['checkoutForm']['finish'] = "txtFinishCO";
    $fieldNames['checkoutForm']['notes'] = "txtaNotesCO";
    
    $fieldNames['locationForm']['id'] = "id";
    $fieldNames['locationForm']['center'] = "selCenter";
    $fieldNames['locationForm']['bldg'] = "txtBldg";
    $fieldNames['locationForm']['room'] = "txtRoom";
    
    $fieldNames['checkinForm']['asset'] = "hidAssetIdCI";
    $fieldNames['checkinForm']['notes'] = "txtaNotesCI";
    
    $fieldNames['restrictForm']['asset'] = "hidAssetIdR";
    $fieldNames['restrictForm']['purpose'] = "txtPurposeR";
    $fieldNames['restrictForm']['notes'] = "txtaNotesR";
    
    $fieldNames['unRestrictForm']['asset'] = "hidAssetIdUR";
    $fieldNames['unRestrictForm']['notes'] = "txtaNotesUR";
    
    if($action == 'new' || $action == 'edit') {
      $fieldNames['assetForm']['id'] = "hidId";
      $fieldNames['assetForm']['model'] = "selModel";
      $fieldNames['assetForm']['location'] = "selLocation";
      $fieldNames['assetForm']['serial'] = "txtSerial";
      $fieldNames['assetForm']['notes'] = "txtaNotes";
      $othFieldNames['attachment'] = "filAttachment";
      $othFieldNames['attachmentNum'] = "hidAttachmentNum";
      $othFieldNames['removeAtt'] = "chkRemoveAtt";
    }

    if(isset($_POST['editModelBtn']) || isset($_POST['addModelBtn'])) {
      if(!isset($_POST['csrfToken']) || $_POST['csrfToken'] !== $csrfToken) {
        // possible CSRF attack
        throw new ISLE\Exception('Possible CSRF attack.', ISLE\Exception::CSRF);
      }

      //redirect to the model form.
      if(isset($_POST['addModelBtn']) ||
         isset($_POST[$fieldNames['assetForm']['model']]) &&
         preg_match('/^[0-9]+$/', $_POST[$fieldNames['assetForm']['model']])) {
        savePOST();
        if(isset($itemToEdit)) {
          $_SESSION['fromItem'] = $itemToEdit;
        }
        if(isset($_POST['editModelBtn'])) {
          header("Location: " . $rootdir . "assetmodels/" .
                 $_POST[$fieldNames['assetForm']['model']]);
        }
        else {
          header("Location: " . $rootdir . "assetmodels/new");
        }
        exit();
      }
      else {
        //todo: show a message to user indicating that they must first select a model to edit.
      }
    }
    
    $svcMethod = '';
    
    if(isset($_POST['addBtn'])) {
      $svcMethod = 'add';
      $successMsg = 'Asset added successfully.';
    }
    elseif(isset($_POST['updateBtn'])) {
      $svcMethod = 'update';
      $successMsg = 'Asset saved successfully.';
    }
    else if(isset($_POST['deleteBtn'])) {
      $svcMethod = 'delete';
      $successMsg = 'Asset deleted successfully.';
    }
    
    if(isset($_POST['addBtn']) || isset($_POST['updateBtn']) ||
       isset($_POST['deleteBtn'])) {
      $class = new ISLE\Models\Asset();
      //todo: refactor into a separate function, or maybe put it in one place like auth.php.
      if(!isset($_POST['csrfToken']) || $_POST['csrfToken'] !== $csrfToken) {
        // possible CSRF attack
        throw new ISLE\Exception('Possible CSRF attack.', ISLE\Exception::CSRF);
      }

      //todo: need to run trim on all the formVals.

      foreach($fieldNames['assetForm'] as $prop => $a) {
        if(isset($_POST[$a]) && strlen($_POST[$a]) > 0) {
          $class->$prop = $_POST[$a];
        }
        else {
          $class->$prop = null;
        }
      }

      try {
        ob_start(); //keeps the page from sending output.
        
        // Validate file upload
        $attachment_location = ISLE\Service::getUploadPath() . '/docs/assets';
        
        if(!isset($_POST['deleteBtn'])) {
 
          // Validate file type
          if($_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_OK &&
             $_FILES[$othFieldNames['attachment']]["tmp_name"] != "") {
            $newfilename = preg_replace('/\.pdf$/', '',
                                        $_FILES[$othFieldNames['attachment']]["name"]);
            $newfilename = preg_replace('/\W/', '', $newfilename);
            $userfile_tmp = $_FILES[$othFieldNames['attachment']]["tmp_name"];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $userfile_tmp);

            switch($mime) {
              case 'application/pdf':
                $fileExt = '.pdf';
                break;
              default:
                //wrong file type.
                $valErrors['attachment'] = 'Attachment must be a pdf.';
                throw new ISLE\UIException('One or more errors occurred', $valErrors);
            }
            
            if(! (intval($_POST[$othFieldNames['attachmentNum']]) >= 0) ||
               $newfilename == '') {
              throw new ISLE\Exception('There was a problem with the attachmentNum or newfilename vars.', ISLE\Exception::UPLOAD);
            }
          }
          
          // Validate file size.
          if($_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_INI_SIZE ||
             $_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_FORM_SIZE) {
            $uploadMax = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');
            $maxSize = $uploadMax - $postMax > 0 ? $postMax : $uploadMax;
            $valErrors['attachment'] = 'Attachment must be under ' . $maxSize . '.';
            throw new ISLE\UIException('One or more errors occurred', $valErrors);
          }

          // Catch other file upload errors.
          if($_FILES[$othFieldNames['attachment']]["error"] > 0 &&
             $_FILES[$othFieldNames['attachment']]["error"] != UPLOAD_ERR_NO_FILE) {
            throw new ISLE\Exception('An unknown error occurred while trying to upload an asset attachment file.', ISLE\Exception::UPLOAD);
          }
          
          if(isset($_POST['updateBtn'])) {
            $addedAsset = $class->id;

            //delete any attachments marked with 'delete'.
            //the highest num for this asset is sent in a hidden field.
            foreach($_POST[$othFieldNames['removeAtt']] as $removeAtt) {
              $pat = '/^' . $addedAsset . '_[1-9][0-9]*\.pdf$/';
              if(preg_match($pat, $removeAtt)) {

                $tmp = explode('.', $removeAtt);
                $attExt = $tmp[1];
                $tmp = explode('_', $tmp[0]);
                $attMod = $tmp[0];
                $attNum = $tmp[1];

                if(!unlink($attachment_location . '/' . $removeAtt)) {
                  throw new ISLE\Exception('Could delete asset attachment.',
                                           ISLE\Exception::UPLOAD);
                }

                $class5 = new ISLE\Models\AssetAttachment();

                $filter['cols'][0]['col'] = 'asset';
                $filter['cols'][0]['val'] = $addedAsset;
                $filter['cols'][1]['col'] = 'num';
                $filter['cols'][1]['val'] = $attNum;
                $filter['cols'][2]['col'] = 'extension';
                $filter['cols'][2]['val'] = $attExt;
                $svc->deleteAll($class5, $filter);
              }
            }
          }
        }
        
        $addedItem = $svc->$svcMethod($class);

        if(isset($_POST['updateBtn']) || isset($_POST['deleteBtn'])) {
          $addedAsset = $class->id;
        }
        else {
          $addedAsset = $addedItem->id;
        }
        
        // File Upload

        //if isset delete btn then delete all attachments for the file.
        //upload any files that were put in the file field to cdn/docs/assets
        
        
        if(isset($_POST['deleteBtn'])) {
          //delete all attachments
          $pat = '/^' . $addedAsset . '_[1-9][0-9]*\.pdf$/';
          
          if ($handle = opendir($attachment_location)) {
            while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                if(preg_match($pat, $entry)) {
                  if(!unlink($attachment_location . '/' . $entry)) {
                    throw new ISLE\Exception('Could delete asset attachment.',
                                             ISLE\Exception::UPLOAD);
                  }
                }
              }
            }
            closedir($handle);
          }
        }
        else {
          // Process Uploaded File
          if($_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_OK &&
             $_FILES[$othFieldNames['attachment']]["tmp_name"] != "") {
            //change name of file. only accept pdf. check contents for mime.
            //disable direct access and use a proxy script to provide access.
            //make sure files do not have execute permission.

            //the highest num for this asset is sent in a hidden field.
            //add 1 to this and use for filename. asset_num.pdf
            $attachmentNum = intval($_POST[$othFieldNames['attachmentNum']]) + 1;
            $attachment_location .= '/' . $addedAsset . '_' . $attachmentNum . $fileExt;
            if(!move_uploaded_file($userfile_tmp, $attachment_location)) {
              throw new ISLE\Exception('Could not move asset attachment to uploads directory.', ISLE\Exception::UPLOAD);
            }

            $class4 = new ISLE\Models\AssetAttachment();
            $class4->asset = $addedAsset;
            $class4->name = $newfilename;
            $class4->num = $attachmentNum;
            $class4->extension = substr($fileExt, 1);
            $svc->add($class4);
          }
        }
        
        // End File Upload
        
        ob_end_clean(); // clear the output and stop buffering.
        
        //set a success message.
        $_SESSION['message']['type'] = 'alert-success';
        $_SESSION['message']['text'] = $successMsg;
        
        //redirect to assets
        if(!isset($_POST['fromJS'])) {
          if(isset($_SESSION['querystring'])){
            header("Location: " . $rootdir . "assets" . $_SESSION['querystring']);
            unset($_SESSION['querystring']);
          }
          else {
            header("Location: " . $rootdir . "assets");
          }
          
        }
        if(isset($_POST['deleteBtn']) && isset($_POST['fromJS'])) {
          //exit('while(1);{"result":{"status":"success", "value":"Asset deleted successfully."}}');
          exit('{"result":{"status":"success", "value":"Asset deleted successfully."}}');
        }
        exit();

      }
      catch(ISLE\UIException $e) {
        //validation failed. show errors.            
        $errors = $e->getValErrors();
      }
      catch(Exception $e) {
        //integrity contraint error
        if(strpos($e->getMessage(), 'SQLSTATE[23000]')) {
          $errors = array('model' => 'That manufacturer/model combination already exists.');
        }
        else {
          throw $e;
        }
      }
    }// end if handle add, update, delete
    else if(isset($_POST['cancelBtn'])) {
      if(isset($_SESSION['querystring'])){
        header("Location: " . $rootdir . "assets" . $_SESSION['querystring']);
        unset($_SESSION['querystring']);
      }
      else {
        header("Location: " . $rootdir . "assets");
      }
      exit();
    }
    else {
      if($action == 'edit') {
        // get the asset from db.
        if(!isset($_POST['editModelBtn'])) {
          $class = new ISLE\Models\Asset();
          $class->id = $itemToEdit;
          try {
            $_SESSION['itemDetails'] = $svc->get($class);
          }
          catch(Exception $e) {
            if(isset($_SESSION['querystring'])){
              header("Location: " . $rootdir . "assets" . $_SESSION['querystring']);
              unset($_SESSION['querystring']);
            }
            else {
              header("Location: " . $rootdir . "assets");
            }
            exit();
          }

          foreach($fieldNames['assetForm'] as $prop => $a) {
            $_POST[$a] = $_SESSION['itemDetails']->$prop;
            
          }
        }
      }
    }

    if($action == 'new' || $action == 'edit' || $action == '') {
      $modelClass = new ISLE\Models\Location();
      $order[0]['col'] = 'center';
      $order[1]['col'] = 'bldg';
      $order[2]['col'] = 'room';
      //get list of locations from db, so ui can load into dropdown.
      $locations = $svc->getAll($modelClass, null, null, null, null, null, $order);
    }

    if($action == 'new' || $action == 'edit') {
      $modelClass = new ISLE\Models\AssetModel();
      $order = array();
      $order[0]['colClass'] = 'Manufacturer';
      $order[0]['col'] = 'name';
      $order[1]['col'] = 'model';
      //get list of asset models from db, so ui can load into dropdown.
      $models = $svc->getAll($modelClass, null, null, null, null, null, $order);

      $selectedModel = '';
      
      if(isset($_SESSION['POST'])) {
        restorePOST();
        $selectedModel = $_POST[$fieldNames['assetForm']['model']];

        if(isset($_SESSION['addedModel'])) {
          $selectedModel = $_SESSION['addedModel'];
          unset($_SESSION['addedModel']);
          //remove session var.
        }
      }
      else {
        if(isset($_POST[$fieldNames['assetForm']['model']])) {
          $selectedModel = $_POST[$fieldNames['assetForm']['model']];
        }
        else if(isset($_SESSION['addedModel'])) {
          $selectedModel = $_SESSION['addedModel'];
          unset($_SESSION['addedModel']);
          //remove session var.
        }
      }
    }
    
    if($action == 'edit') {
      
      //get attachments from db.
      $attachmentsClass = new ISLE\Models\AssetAttachment();
      $filter['cols'][0]['col'] = 'asset';
      $filter['cols'][0]['val'] = $itemToEdit;
      $order[0]['col'] = 'num';
      //get list of attachments from db, so ui can load into dropdown.
      $attachments = $svc->getAll($attachmentsClass, null, null, null, null,
                                  $filter, $order);
    }
    
    if($action == '') {
      //get categories from database.
      $modelClass = new ISLE\Models\Category();
      $order[0]['col'] = 'name';
      $categories = $svc->getAll($modelClass, null, null, null, null, null, $order);

      $catItems = array();
      $catParents = array();
      foreach($categories as $item) {
        $catItems[$item['id']] = $item;
        $catParents[$item['parent']][] = $item['id'];
      }
    }
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                        " - Assets</title>";
    
    switch($action) {
      case 'new':
        $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                            " - Add Asset</title>";
        break;
      case 'edit':
        $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                            " - Edit Asset " . $itemToEdit . "</title>";
        break;
    }
    
    $tmpl_headcontent .= $tmpl_headcontent_main;
    if ($action == '') {
      $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' .
                           auto_version($stylesPath . 'list_ie7.css') .
                           '" /><![endif]-->';
      $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' .
                           auto_version($stylesPath . 'list.css') .
                           '" /><!--<![endif]-->';
    }
    else {
      $tmpl_headcontent .= '<link rel="stylesheet" type="text/css" href="' .
                           auto_version($stylesPath . 'views/assets.css') . '" />';
    }
    
    $tmpl_javascripts = $tmpl_javascripts_main;
    if($action == '') {
      $dontShowMsg = true;
    }
    require_once $layoutsPath . 'pagestart.php';
    
    if($action == 'new' || $action == 'edit') {
      require_once $viewsPath . 'assetForm.php';
    }
    else if ($action == '') {
      require_once $viewsPath . 'assets.php';
    }
    
    if(!isset($_POST['fromJS'])) {
      if(isset($_SESSION['querystring'])){
        echo '<script>var querystring = "' . javascript_escape($_SESSION['querystring']) .
             '";</script>';
      }
      else {
        echo '<script>var querystring = "";</script>';
      }
    }
    
    require_once $layoutsPath . 'pageend.php'; 
?>
