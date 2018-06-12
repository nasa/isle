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
    
    if($action != 'new' && $action != 'edit') {
      // redirect to 404 page if no action exists.
      throw new ISLE\Exception('The requested assetmodel action is invalid.',
                               ISLE\Exception::FOUROFOUR);
    }
    
    if($u['role'] <= ISLE\Models\Role::USER) {
      $_SESSION['message']['type'] = 'alert-error';
      $_SESSION['message']['text'] = 'You do not have permission to do that.';
      header("Location: " . $rootdir . "assets");
      exit();
    }
    
    // Setup an array or structure that contains the field names, so you only
    // have one place to update if you want to change them.
    $fieldNames['assetModelForm']['id'] = "hidId";
    $fieldNames['assetModelForm']['desc'] = "txtDesc";
    $fieldNames['assetModelForm']['model'] = "txtModel";
    $fieldNames['assetModelForm']['series'] = "txtSeries";
    $fieldNames['assetModelForm']['mfr'] = "selMfr";
    $fieldNames['assetModelForm']['url'] = "txtUrl";
    $fieldNames['assetModelForm']['img'] = "hidImg";
    $othFieldNames['image'] = "filImage";
    $othFieldNames['attachment'] = "filAttachment";
    $othFieldNames['attachmentNum'] = "hidAttachmentNum";
    $othFieldNames['removeAtt'] = "chkRemoveAtt";
    $othFieldNames['removeImage'] = "chkRemoveImg";
    $othFieldNames['categories'] = "txtCategories";
    $othFieldNames['categoryLabels'] = "hidCategoryLabels";
    
    $fieldNames['manufacturerForm']['id'] = "hidId";
    $fieldNames['manufacturerForm']['name'] = "txtName";
    $fieldNames['manufacturerForm']['url'] = "txtUrl";
    
    $fieldNames['attributesForm']['id'] = "hidId";
    $fieldNames['attributesForm']['model'] = "hidModel";
    $fieldNames['attributesForm']['attribute'] = "selAttribute";
    $fieldNames['attributesForm']['value'] = "txtValue";
    
    $fieldNames['relationshipsForm']['id'] = "hidIdR";
    $fieldNames['relationshipsForm']['source'] = "selSource";
    $fieldNames['relationshipsForm']['relation'] = "selRelation";
    $fieldNames['relationshipsForm']['target'] = "selTarget";
    
    $fieldNames['relationForm']['id'] = "hidId";
    $fieldNames['relationForm']['name'] = "txtName";

    $svcMethod = '';
    
    if(isset($_POST['addBtn'])) {
      $svcMethod = 'add';
      $successMsg = 'Model added successfully.';
    }
    elseif(isset($_POST['updateBtn'])) {
      $svcMethod = 'update';
      $successMsg = 'Model saved successfully.';
    }
    else if(isset($_POST['deleteBtn'])) {
      $svcMethod = 'delete';
      $successMsg = 'Model deleted successfully.';
    }
    
    if(isset($_POST['addBtn']) || isset($_POST['updateBtn']) || isset($_POST['deleteBtn'])) {
      
      $class = new ISLE\Models\AssetModel();
      //todo: refactor into a separate function, or maybe put it in one place like auth.php.
      if(!isset($_POST['csrfToken']) || $_POST['csrfToken'] !== $csrfToken) {
        // possible CSRF attack
        throw new ISLE\Exception('Possible CSRF attack.', ISLE\Exception::CSRF);
      }

      //todo: need to run trim on all the formVals.

      foreach($fieldNames['assetModelForm'] as $prop => $a) {       
        if(isset($_POST[$a]) && strlen($_POST[$a]) > 0) {
          $class->$prop = $_POST[$a];
        }
        else {
          $class->$prop = null;
        }
      }
      
      //if remove image check box is checked then set $class->img = null.
      if(isset($_POST[$othFieldNames['removeImage']])) {
        $class->img = null;
        $class->img_modified = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
      }

      if(isset($_POST['deleteBtn'])) {
        $class6 = new ISLE\Models\Asset();
        $filter['cols'][0]['col'] = 'model';
        $filter['cols'][0]['val'] = $class->id;
        $assets = $svc->getAll($class6, null, null, null, null, $filter, null);
        $assetIds = array();

        foreach($assets as $asset) {
          $assetIds[] = $asset['id'];
        }
      }

      try {
        ob_start(); //keeps the page from sending output.
 
        // Validate file upload
        $attachment_location = ISLE\Service::getUploadPath() . '/docs/assetmodels';
        
        if(!isset($_POST['deleteBtn'])) {
 
          // Validate file type
          if ($_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_OK and
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
                $valErrors['attachment'] = 'Attachment must be a PDF.';
                throw new ISLE\UIException('One or more errors occurred', $valErrors);
            }
            
            if(! (intval($_POST[$othFieldNames['attachmentNum']]) >= 0) or
               $newfilename == '') {
              throw new ISLE\Exception('There was a problem with the attachmentNum or newfilename vars.', ISLE\Exception::UPLOAD);
            }
          }
          
          // Validate file size.
          if($_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_INI_SIZE or
             $_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_FORM_SIZE) {
            $uploadMax = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');
            $maxSize = $uploadMax - $postMax > 0 ? $postMax : $uploadMax;
            $valErrors['attachment'] = 'Attachment must be under ' . $maxSize . '.';
            throw new ISLE\UIException('One or more errors occurred', $valErrors);
          }

          // Catch other file upload errors.
          if($_FILES[$othFieldNames['attachment']]["error"] > 0 and
             $_FILES[$othFieldNames['attachment']]["error"] != UPLOAD_ERR_NO_FILE) {
            throw new ISLE\Exception('An unknown error occurred while trying to upload an asset attachment file.', ISLE\Exception::UPLOAD);
          }
          
          // Validate Image Upload
          
          if(!isset($_POST[$othFieldNames['removeImage']])) {
            
            // Validate file type.
            if($_FILES[$othFieldNames['image']]["error"] == UPLOAD_ERR_OK and
               $_FILES[$othFieldNames['image']]["tmp_name"] != "") {
              $userfile_tmp = $_FILES[$othFieldNames['image']]["tmp_name"];
              $finfo = finfo_open(FILEINFO_MIME_TYPE);
              $mime = finfo_file($finfo, $userfile_tmp);

              switch($mime) {
                case 'image/jpeg':
                  $fileExt = '.jpg';
                  break;
                case 'image/gif':
                  $fileExt = '.gif';
                  break;
                case 'image/png':
                  $fileExt = '.png';
                  break;
                default:
                  //wrong file type.
                  $valErrors['image'] = 'Image must be jpg, gif, or png.';
                  throw new ISLE\UIException('One or more errors occurred', $valErrors);
              }
            }
            
            // Validate file size.
            if($_FILES[$othFieldNames['image']]["error"] == UPLOAD_ERR_INI_SIZE or
               $_FILES[$othFieldNames['image']]["error"] == UPLOAD_ERR_FORM_SIZE) {
              $uploadMax = ini_get('upload_max_filesize');
              $postMax = ini_get('post_max_size');
              $maxSize = $uploadMax - $postMax > 0 ? $postMax : $uploadMax;
              $valErrors['attachment'] = 'Image must be under ' . $maxSize . '.';
              throw new ISLE\UIException('One or more errors occurred', $valErrors);
            }

            // Catch other file upload errors.
            if($_FILES[$othFieldNames['image']]["error"] > 0 and
               $_FILES[$othFieldNames['image']]["error"] != UPLOAD_ERR_NO_FILE) {
              throw new ISLE\Exception('An unknown error occurred while trying to upload an asset model image.', ISLE\Exception::UPLOAD);
            }
          }
          
          if(isset($_POST['updateBtn'])) {
            $_SESSION['addedModel'] = $class->id;

            // Delete any attachments marked with 'delete'.
            // The highest num for this model is sent in a hidden field.
            foreach($_POST[$othFieldNames['removeAtt']] as $removeAtt) {
              $pat = '/^' . $_SESSION['addedModel'] . '_[1-9][0-9]*\.pdf$/';
              if(preg_match($pat, $removeAtt)) {

                $tmp = explode('.', $removeAtt);
                $attExt = $tmp[1];
                $tmp = explode('_', $tmp[0]);
                $attMod = $tmp[0];
                $attNum = $tmp[1];
                
                if(!unlink($attachment_location . '/' . $removeAtt)) {
                  throw new ISLE\Exception('Could not delete assetmodel attachment.',
                                           ISLE\Exception::UPLOAD);
                }

                $class5 = new ISLE\Models\AssetModelAttachment();

                $filter['cols'][0]['col'] = 'model';
                $filter['cols'][0]['val'] = $_SESSION['addedModel'];
                $filter['cols'][1]['col'] = 'num';
                $filter['cols'][1]['val'] = $attNum;
                $filter['cols'][2]['col'] = 'extension';
                $filter['cols'][2]['val'] = $attExt;
                $svc->deleteAll($class5, $filter);
              }
            }
          }
        }
        
        $addedModel = $svc->$svcMethod($class);

        // Store the newly added/saved model id in session so it can be used
        // on the new asset form.
        if(isset($_POST['updateBtn']) || isset($_POST['deleteBtn'])) {
          $_SESSION['addedModel'] = $class->id;
        }
        else {
          $_SESSION['addedModel'] = $addedModel->id;
        }
        
        if(!isset($_POST['deleteBtn'])) {
          $categories = explode(',', $_POST[$othFieldNames['categories']]);
          $class2 = new ISLE\Models\AssetModelCategory();
          $filter['cols'][0]['col'] = 'model';
          $filter['cols'][0]['val'] = $_SESSION['addedModel'];
          $svc->deleteAll($class2, $filter);
          foreach($categories as $category) {
            if($category != "") {
              $class2->category = $category;
              $class2->model = $_SESSION['addedModel'];
              $svc->add($class2);
            }
          }
        }
        
        //Image Upload
        
        //google file upload security and follow guidelines.
        //add an .htaccess file with whitelist of extensions to allow access to. put in images directory.
        //validate the image contents (getimagesize == true) to make sure it's an image and has a proper file extension (white list). check mime type. create a file extension based on mime type.
        //resize the image to the max size if its over it. 800x800.
        //rename image as a hash of filename and put the image in cdn/images/assetmodels
        //make a smaller copy of the image and put in assetmodels/thumbs
        
        $large_image_location = ISLE\Service::getUploadPath() . '/images/assetmodels';
        $thumb_image_location = $large_image_location . '/thumbs';
        
        //if delete btn was clicked delete the images.
        //if remove image check box was checked then delete images
        if(isset($_POST['deleteBtn']) || isset($_POST[$othFieldNames['removeImage']])) {
          
          //delete large image
          if ($handle = opendir($large_image_location)) {
            while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != ".." && $entry != "thumbs") {
                $path_parts = pathinfo($entry);
                if($path_parts['filename'] == $_SESSION['addedModel']) {
                  if(!unlink($large_image_location . '/' . $entry)) {
                    //todo: asset_models.img was already set to null so need to put it back to what it was since the image wasn't actually deleted.
                    throw new ISLE\Exception('Could not delete assetmodel large image.', ISLE\Exception::UPLOAD);
                  }
                }
              }
            }
            closedir($handle);
          }
          
          //delete thumbnail
          if ($handle = opendir($thumb_image_location)) {
            while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                $path_parts = pathinfo($entry);
                if($path_parts['filename'] == $_SESSION['addedModel']) {
                  if(!unlink($thumb_image_location . '/' . $entry)) {
                    throw new ISLE\Exception('Could not delete assetmodel thumb image.', ISLE\Exception::UPLOAD);
                  }
                }
              }
            }
            closedir($handle);
          }
        }
        else {
          // Process Uploaded Image
          if($_FILES[$othFieldNames['image']]["error"] == UPLOAD_ERR_OK && $_FILES[$othFieldNames['image']]["tmp_name"] != "") {

            $large_image_filename = $large_image_location . '/' . $_SESSION['addedModel'] . $fileExt;
            $thumb_image_filename = $large_image_location . '/thumbs/' . $_SESSION['addedModel'] . $fileExt;

            if(!move_uploaded_file($userfile_tmp, $large_image_filename)) {
              throw new ISLE\Exception('Could not move assetmodel large image to uploads directory.', ISLE\Exception::UPLOAD);
            }

            //delete the other file.
            if ($handle = opendir($large_image_location)) {
              while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != "thumbs") {
                  $path_parts = pathinfo($entry);
                  if($path_parts['filename'] ==  $_SESSION['addedModel'] && $entry != $_SESSION['addedModel'] . $fileExt) {
                    if(!unlink($large_image_location . '/' . $entry)) {
                      throw new ISLE\Exception('Could not delete old assetmodel large image.', ISLE\Exception::UPLOAD);
                    }
                  }
                }
              }
              closedir($handle);
            }

            $image = new Imagick( $large_image_filename );
            $imageprops = $image->getImageGeometry();
            if(!($imageprops['width'] <= 800)) {
              $image->resizeImage(800,0, imagick::FILTER_LANCZOS, 1);
              if(!$image->writeimage($large_image_filename)) {
                throw new ISLE\Exception('Could not write resized assetmodel large image using Imagick.', ISLE\Exception::UPLOAD);
              }
            }
            if(!($imageprops['width'] <= 100)) {
              $image->resizeImage(100,0, imagick::FILTER_LANCZOS, 1);
            }
            if(!$image->writeimage($thumb_image_filename)) {
              throw new ISLE\Exception('Could not write resized assetmodel thumb image using Imagick.', ISLE\Exception::UPLOAD);
            }
            $image->destroy();

            //delete the other file.
            if ($handle = opendir($thumb_image_location)) {
              while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                  $path_parts = pathinfo($entry);
                  if($path_parts['filename'] == $_SESSION['addedModel'] && $entry != $_SESSION['addedModel'] . $fileExt) {
                    if(!unlink($thumb_image_location . '/' . $entry)) {
                      throw new ISLE\Exception('Could not delete old assetmodel thumb image.', ISLE\Exception::UPLOAD);
                    }
                  }
                }
              }
              closedir($handle);
            }

            //update db and set asset_models.img to file ext.
            $class3 = $class;
            $class3->id = $_SESSION['addedModel'];
            $class3->img = substr($fileExt, 1);
            $class3->img_modified = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
            $svc->update($class3);
          }
        }
        
        // End Image Upload
        
        // File Upload

        //if isset delete btn then delete all attachments for the file.
        //then upload any files that were put in the file field to cdn/docs/assetmodels

        if(isset($_POST['deleteBtn'])) {
          //delete all attachments
          
          $pat = '/^' . $_SESSION['addedModel'] . '_[1-9][0-9]*\.pdf$/';
          
          if ($handle = opendir($attachment_location)) {
            while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                if(preg_match($pat, $entry)) {
                  if(!unlink($attachment_location . '/' . $entry)) {
                    throw new ISLE\Exception('Could not delete assetmodel attachment.', ISLE\Exception::UPLOAD);
                  }
                }
              }
            }
            closedir($handle);
          }
          
          //delete all attachments for assets with this model type.
          $pat = '/^(' . implode('|', $assetIds) . ')_[1-9][0-9]*\.pdf$/';
          $asset_attachment_location = ISLE\Service::getUploadPath() . '/docs/assets';
          
          if ($handle = opendir($asset_attachment_location)) {
            while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                if(preg_match($pat, $entry)) {
                  if(!unlink($asset_attachment_location . '/' . $entry)) {
                    throw new ISLE\Exception('Could not delete asset attachment.', ISLE\Exception::UPLOAD);
                  }
                }
              }
            }
            closedir($handle);
          }
        }
        else {
          // Process Uploaded File
          if($_FILES[$othFieldNames['attachment']]["error"] == UPLOAD_ERR_OK && $_FILES[$othFieldNames['attachment']]["tmp_name"] != "") {
            // Change name of file. Only accept pdf. Check contents for mime.
            // Disable direct access and use a proxy script to provide access.
            // Make sure files do not have execute permission.
              
            // The highest num for this model is sent in a hidden field. Add 1
            // to this and use for filename. model_num.pdf
            $attachmentNum = intval($_POST[$othFieldNames['attachmentNum']]) + 1;
            $attachment_location .= '/' . $_SESSION['addedModel'] . '_' . $attachmentNum . $fileExt;
            if(!move_uploaded_file($userfile_tmp, $attachment_location)) {
              throw new ISLE\Exception('Could not move assetmodel attachment to uploads directory.', ISLE\Exception::UPLOAD);
            }

            $class4 = new ISLE\Models\AssetModelAttachment();
            $class4->model = $_SESSION['addedModel'];
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
        
        $_SESSION['fromModel'] = true;
        
        if(!isset($_POST['fromJS'])) {
          //go back to either assets/new or assets/id.
          $goto = 'assets/new';
          if(isset($_SESSION['fromItem'])) {
            $goto = "assets/" . $_SESSION['fromItem'];
            unset($_SESSION['fromItem']);
          }
          header("Location: " . $rootdir . $goto);
        }
        else {
          unset($_SESSION['fromItem']);
        }
        
        if(isset($_POST['deleteBtn']) && isset($_POST['fromJS'])) {
          //exit('while(1);{"result":{"status":"success", "value":"Asset model deleted successfully."}}');
          exit('{"result":{"status":"success", "value":"Asset model deleted successfully."}}');
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
        else{
          throw $e;
        }
      }
    }// end if handle add, update, delete
    else if(isset($_POST['cancelBtn'])) {
      if(isset($itemToEdit)) {
        $_SESSION['addedModel'] = $itemToEdit;
      }
      //go back to either assets/new or assets/id.
      $goto = 'assets/new';
      if(isset($_SESSION['fromItem'])) {
        $goto = "assets/" . $_SESSION['fromItem'];
        unset($_SESSION['fromItem']);
      }
      $_SESSION['fromModel'] = true;
      header("Location: " . $rootdir . $goto);
      exit();
    }
    else {
      switch($action) {
        case 'edit':

          // get the model from db.
          $modelClass = new ISLE\Models\AssetModel();
          $modelClass->id = $itemToEdit;
          try {
            $modelDetails = $svc->get($modelClass);
          }
          catch(Exception $e) {
            // redirect to 404 page.
            throw new ISLE\Exception($e->getMessage(), ISLE\Exception::FOUROFOUR, $e);
          }

          foreach($fieldNames['assetModelForm'] as $prop => $a) {
            $_POST[$a] = $modelDetails->$prop;
          }
          
          break;
      }
    }
    
    if($action == 'edit') {
      //get attachments from db.
      $attachmentsClass = new ISLE\Models\AssetModelAttachment();
      $filter['cols'][0]['col'] = 'model';
      $filter['cols'][0]['val'] = $itemToEdit;
      $order[0]['col'] = 'num';
      //get list of attachments from db, so ui can load into dropdown.
      $attachments = $svc->getAll($attachmentsClass, null, null, null, null, $filter, $order);
      
      if(!empty($_POST[$fieldNames['assetModelForm']['img']])) {
        $modelImg['source'] = $rootdir . 'uploads/images/assetmodels/thumbs/' .
                              $itemToEdit . '.' .
                              $_POST[$fieldNames['assetModelForm']['img']] .
                              '?ts=' . strtotime($modelDetails->img_modified);
        $modelImg['target'] = $rootdir . 'uploads/images/assetmodels/' .
                              $itemToEdit . '.' .
                              $_POST[$fieldNames['assetModelForm']['img']] .
                              '?ts=' . strtotime($modelDetails->img_modified);
      }
    }
    
    $manufacturersClass = new ISLE\Models\Manufacturer();
    $order[0]['col'] = 'name';
    //get list of manufacturers from db, so ui can load into dropdown.
    $manufacturers = $svc->getAll($manufacturersClass, null, null, null, null, null, $order);
    
    if($action == 'edit') {
      $modelClass = new ISLE\Models\Attribute();
      $order[0]['col'] = 'name';
      //get list of attributes from db, so ui can load into dropdown.
      $attributes = $svc->getAll($modelClass, null, null, null, null, null, $order);

      $modelClass = new ISLE\Models\Relation();
      $order[0]['col'] = 'name';
      //get list of relations from db, so ui can load into dropdown.
      $relations = $svc->getAll($modelClass, null, null, null, null, null, $order);
      
      $modelClass = new ISLE\Models\AssetModel();
      $order = array();
      $order[0]['colClass'] = 'Manufacturer';
      $order[0]['col'] = 'name';
      $order[1]['col'] = 'model';
      //get list of asset models from db, so ui can load into dropdown.
      $models = $svc->getAll($modelClass, null, null, null, null, null, $order);
    }
    
    $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                        " - Asset Models</title>";
    
    switch($action) {
      case 'new':
        $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                            " - Add Asset Model</title>";
        break;
      case 'edit':
        $tmpl_headcontent = "<title>ISLE: " . ISLE\Service::getInstanceName() .
                            " - Edit Asset Model " . $itemToEdit . "</title>";
        break;
    }
    
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<link rel="stylesheet" type="text/css" href="' .
                         auto_version($stylesPath . 'views/assets.css') . '" />';
    $tmpl_headcontent .= '<link rel="stylesheet" type="text/css" href="' .
                         auto_version($stylesPath . 'jquery.tagit.css') . '" />';
    
    $tmpl_javascripts = $tmpl_javascripts_main;

    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'assetModelForm.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
