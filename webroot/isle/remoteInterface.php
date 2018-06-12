<?php
  use ISLE\Secrets;

  // Responds to incoming requests.
  //
  // API:
  //   method=logout
  //   method=feedback
  //                  &type=bug&steps=S            # length of S <= 2000
  //                  &type=feature&description=D[&attachment=?]
  //                  &type=chore&description=D[&attachment=?]
  //   method=add
  //             &model=User[&chkEmail=EMAIL]
  //   method=update
  //   method=delete
  //                &model=Version
  //                &model=User
  //                &model=MODEL_NAME
  //                &model=TransactionCheckout
  //                &model=TransactionCheckin
  //                &model=TransactionRestrict
  //                &model=TransactionUnrestrict
  //   method=getAll
  //                &model=Version[&filter=?]
  //                &model=Asset[&filter=?][&tree=TF]
  //                &model=Transaction[&filter=?][&tree=TF]
  //   method=getForeignKeyReferences&nodeId=ID
  //
  // Note: $_REQUEST contains $_POST

  try {
    require_once 'includes/auth.php';

    //todo: remove this once logging has been enabled on the dev server in php.ini.
    if ( defined( 'SERVER_INSTANCE' ) && SERVER_INSTANCE == 'dev' ) { // dev
      ini_set('display_errors',0);
    }
    header('Content-type: text/javascript');

    // Check header for csrfToken. If not valid don't procede.
    $reqHeaders = getallheaders();
    if(!isset($reqHeaders['x-csrftoken']) ||
       $reqHeaders['x-csrftoken'] !== $csrfToken) {
      throw new ISLE\Exception('Possible CSRF attack.', ISLE\Exception::CSRF);
    }

/*
ob_start();
var_dump($_REQUEST);
error_log(ob_get_contents());
ob_end_clean();

ob_start();
var_dump($_POST);
error_log(ob_get_contents());
ob_end_clean();
*/

    // Check for logout:
    //   method=logout
    if($_REQUEST['method'] == 'logout') {
      session_destroy();
      exit(prefixJSON(2,json_encode('success')));
    }

    // Check for feedback from user:
    //   method=feedback
    //                  &type=bug&steps=S            # length of S <= 2000
    //                  &type=feature&description=D[&attachment=?]
    //                  &type=chore&description=D[&attachment=?]
    if($_REQUEST['method'] == 'feedback') {
      $formVals = $_POST['args'][0];		// Maps HTML form fields to values.
      $fieldNames = $_POST['args'][1];		// Maps DB fields to HTML form fields.
      $valErrors = array();

      //validate fields
      switch($formVals[$fieldNames['type']]) {
        case 'bug':
          if(isset($formVals[$fieldNames['steps']]) &&
             strlen($formVals[$fieldNames['steps']]) > 2000) {
            $valErrors['steps'] = '2000 characters max.';
          }
        case 'feature':
        case 'chore':
          if(empty($formVals[$fieldNames['description']])) {
            $valErrors['description'] = 'Description is required.';
          }
          else if(strlen($formVals[$fieldNames['description']]) > 2000) {
            $valErrors['description'] = '2000 characters max.';
          }
          break;
        default:
          throw new ISLE\Exception('Invalid feedback type.', ISLE\Exception::AJAX);
      }

      // Process Uploaded Attachment
      if($_FILES[$fieldNames['attachment']]["tmp_name"] != "") {
        $newfilename = preg_replace('/(\.pdf|\.jpg|\.jpeg|\.png|\.gif)$/',
                                    '', $_FILES[$fieldNames['attachment']]["name"]);
        $newfilename = preg_replace('/\W/', '', $newfilename);
        $userfile_tmp = $_FILES[$fieldNames['attachment']]["tmp_name"];
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
          case 'application/pdf':
            $fileExt = '.pdf';
            break;
          default:
            //wrong file type.
            $valErrors['attachment'] = 'Only .jpg, .gif, .png, and .pdf files allowed.';
        }
      }

      if(!empty($valErrors)) {
        try {
          throw new ISLE\UIException('One or more errors occurred', $valErrors);
        }
        catch(ISLE\UIException $e) {
          //validation failed. show errors.
          exit(prefixJSON(1,json_encode($e->getValErrors())));
        }
      }

      $name = '';
      $desc = '';

      switch($formVals[$fieldNames['type']]) {
        case 'bug':
          if(strpos($_SERVER['HTTP_REFERER'], $rootdir) !== false) {
            $name = substr($_SERVER['HTTP_REFERER'],
                           strpos($_SERVER['HTTP_REFERER'], $rootdir) +
                           strlen($rootdir));
          }
          $desc = 
'*What happened?*
'.$formVals[$fieldNames['description']].'

*Steps to reproduce*
'.$formVals[$fieldNames['steps']];
          break;
        case 'feature':
        case 'chore':
          if(isset($u['name'])) {
            $name = $u['name'];
          }
          $desc = $formVals[$fieldNames['description']];
          break;
      }

      $page = '';
      $subBy = '';
      $userAgent = '';

      if(isset($_SERVER['HTTP_REFERER'])) {
        $page = $_SERVER['HTTP_REFERER'];
      }
      if(isset($u['name'])) {
        $subBy = $u['name'];
      }
      if(isset($_SERVER['HTTP_USER_AGENT'])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
      }

      $desc .= '

*Details*
_Page:_               '.$page.'
_Date:_                '.date('d-M-Y H:i:s e', time()).'
_Submitted by:_ '.$subBy.'
_User-Agent:_    '.$userAgent;

      // config-todo: set to your pivotal tracker token.
      $custom_headers = array('X-TrackerToken' => 'SET_TOKEN_HERE');

      $xmldata = '<story><story_type>' . $formVals[$fieldNames['type']] . '</story_type><name>' . date('[n/j/Y g:i:sa]', time()) . ' ' . htmlspecialchars($name) . '</name><description>' . htmlspecialchars($desc) . '</description></story>';

      // config-todo: set to your project ID
      $httpResponse = ISLE\Validate::http_request('POST', 'www.pivotaltracker.com', 443, '/services/v3/projects/YOUR_PROJECT_ID/stories', array(), array(), $xmldata, array(), array(), $custom_headers, 1, false, false);

      //check the response for a single <story> node. This indicates success.
      $xmlParser = xml_parser_create();
      xml_parse_into_struct($xmlParser, $httpResponse, $xmlResp);
      if($xmlResp[0]['tag'] == 'STORY') {
        if($xmlResp[1]['tag'] != 'ID' || !preg_match('/^[0-9]+$/', $xmlResp[1]['value'])){
          throw new ISLE\Exception('An error occurred while submitting feedback.', ISLE\Exception::AJAX);
        }

        // story successfully submitted, now upload the attachment if there is one.
        if(isset($newfilename)) {
          $storyId = $xmlResp[1]['value'];
          $formdata = array();
          $formdata[] = 'Content-Disposition: form-data; name="Filedata"; filename="'.$newfilename.$fileExt.'"';
          $formdata[] = 'Content-Type: '.$_FILES[$fieldNames['attachment']]["type"];
          $formdata['formVal'] = file_get_contents($_FILES[$fieldNames['attachment']]["tmp_name"]);
          // config-todo: set to your project ID
          $httpResponse = ISLE\Validate::http_request('POST', 'www.pivotaltracker.com', 443, '/services/v3/projects/YOUR_PROJECT_ID/stories/'.$xmlResp[1]['value'].'/attachments', array(), array(), NULL, $formdata, array(), $custom_headers, 1, false, false);

          $xmlParser = xml_parser_create();
          xml_parse_into_struct($xmlParser, $httpResponse, $xmlResp);

          $isError = true;

          foreach($xmlResp as $value) {
            if($value['tag'] == 'STATUS' && strtolower($value['value']) == 'pending') {
              $isError = false;
              break;
            }
          }

          if($xmlResp[0]['tag'] != 'ATTACHMENT' || $isError) {
            // If the attachment upload is successful then send back a success
            // msg. If it's not send back a failure message, and delete the story.
            // delete story.
            // config-todo: set to your project ID
            $httpResponse = ISLE\Validate::http_request('DELETE',
                                                        'www.pivotaltracker.com',
                                                        443,
                                                        '/services/v3/projects/YOUR_PROJECT_ID/stories/'.$storyId,
                                                        array(), array(), NULL,
                                                        array(), array(),
                                                        $custom_headers, 1, false,
                                                        false);
            throw new ISLE\Exception('An error occurred while submitting feedback attachment.', ISLE\Exception::AJAX);
          }
        }
        exit(prefixJSON(2, json_encode('Feedback successfully submitted.')));
      }

      throw new ISLE\Exception('An error occurred while submitting feedback.',
                               ISLE\Exception::AJAX);
    } // End feedback.

    // Whitelist validate GET model.  Model name must not have underscores or numbers.
    if(!preg_match('/^[a-zA-Z]+$/', $_REQUEST['model'])) {
      throw new ISLE\Exception('Invalid Model', ISLE\Exception::AJAX);
    }

    // If model isn't "Version", then it is the name of an MVC model:
    if($_REQUEST['model'] !== 'Version') {
      eval('$class = new ISLE\\Models\\' . $_REQUEST['model'] . '();');
    }

    switch($_REQUEST['method']) {

      case 'add':
      case 'update':
      case 'delete':

        $formVals = $_POST['args'][0];		// Maps HTML form fields to values.
        $fieldNames = $_POST['args'][1];	// Maps DB fields to HTML form fields.

        switch($_REQUEST['model']) {
          case 'User':
            //if we're adding a user.
            if($_REQUEST['method'] == 'add') {
              if(isset($formVals['chkEmail'])) {
                //validate the email.
                try {
                  //set a flag telling the script to send the welcome message.
                  $validEmail = ISLE\Validate::email($formVals['chkEmail']);
                }
                catch(Exception $e) { }
              }
            }
            break;
          case 'TransactionCheckout':
          case 'TransactionCheckin':
          case 'TransactionRestrict':
          case 'TransactionUnrestrict':
            $fieldNames['user'] = 'svrUser';
            $fieldNames['time'] = 'svrTime';
            $fieldNames['type'] = 'svrType';
            $formVals[$fieldNames['user']] = $u['id'];
            $formVals[$fieldNames['time']] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
            $assetIds = explode(',', $formVals[$fieldNames['asset']]);
            if(count($assetIds) > 1) {
              $multiple = true;
            }
        }

        if($_REQUEST['model'] == 'TransactionCheckout') {
          $formVals[$fieldNames['type']] = 1;
        }
        else if($_REQUEST['model'] == 'TransactionCheckin') {
          $formVals[$fieldNames['type']] = 2;
        }
        else if($_REQUEST['model'] == 'TransactionRestrict') {
          $formVals[$fieldNames['type']] = 3;
        }
        else if($_REQUEST['model'] == 'TransactionUnrestrict') {
          $formVals[$fieldNames['type']] = 4;
        }

        //todo: need to run trim on all the formVals.

        if(isset($multiple)){
          foreach($assetIds as $id) {
            foreach($fieldNames as $prop => $a) {
              if($prop == 'asset') {
                $class->$prop = $id;
              }
              else if(strlen($formVals[$a]) > 0) {
                $class->$prop = $formVals[$a];
              }
              else {
                $class->$prop = null;
              }
            }

            try {
              $addedItem = $svc->$_POST['method']($class);
            }
            catch(ISLE\UIException $e) {
              //validation failed. show errors.
              exit(prefixJSON(1,json_encode($e->getValErrors())));
            }
          }
        }
        else {
          foreach($fieldNames as $prop => $a) {
            if(strlen($formVals[$a]) > 0) {
              $class->$prop = $formVals[$a];
            }
            else {
              $class->$prop = null;
            }
          }

          try {
            $addedItem = $svc->$_POST['method']($class);
          }
          catch(ISLE\UIException $e) {
            //validation failed. show errors.
            exit(prefixJSON(1,json_encode($e->getValErrors())));
          }

        }

        if(isset($validEmail)) {
          //send the welcome message to the added user's email.

          $to  = $validEmail;
          $subject = 'Welcome to ISLE';

          //config-todo: set to your url.
          $eol = "\r\n";
          $message =
"<html>" . $eol .
"  <head>" . $eol .
"    <title>Welcome to ISLE</title>" . $eol .
"  </head>" . $eol .
"  <body>" . $eol .
"    <p>Hi " . htmlspecialchars($formVals[$fieldNames['name']]) . ",</p>" . $eol .
"    <p>&nbsp;&nbsp;You've been given an account on the ISLE application." .
"       You can access the application at the following URL:</p>" . $eol .
"    <p><a href='" . Secrets::get_url() . "'>" . Secrets::get_url() . "</a><br/></p>" .
"    <p>" . Secrets::ADMIN_NAME . "</p>" . $eol .
"  </body>" . $eol .
"</html>";

          $headers  = 'MIME-Version: 1.0' . $eol;
          $headers .= 'Content-Type: text/html; charset=iso-8859-1' . $eol;
          //config-todo: set your name and email here.
          $headers .= 'From: ' . Secrets::ADMIN_NAME . ' <' . Secrets::ADMIN_EMAIL .
                      '>' . $eol;

          mail($to, $subject, $message, $headers);
        }

        exit(prefixJSON(2, json_encode($addedItem)));

        break;
      case 'getAll':
        //if $_REQUEST['model'] = 'Version' than use the local array of Versions.
        //set $ret['count'] = array len of Version array.
        //set $ret['items'] to the Version array.
        if($_REQUEST['model'] == 'Version') {
          if(isset($_REQUEST['filter'][0]['cols'][0]['val'])) {
            //look up the version based on the filter.
            $rows = getVersions($u, $_REQUEST['filter'][0]['cols'][0]['val']);
          }
          else {
            $rows = getVersions($u);
          }
          $ret['count'] = count($rows);
          $ret['items'] = $rows;
        }
        else {
          $countMethod = 'count';
          if($_REQUEST['model'] == 'Asset') {
            $_REQUEST['method'] = 'getAllAssets';
            $countMethod = 'countAssets';
          }
          else if($_REQUEST['model'] == 'Transaction') {
            $class = new ISLE\Models\TransactionCheckout();
          }

          $res = $svc->$countMethod($class, $_REQUEST['filter']);
          $ret['count'] = $res['total'];

          $rows = $svc->$_REQUEST['method']($class, $_REQUEST['start'],
                                            $_REQUEST['limit'], $_REQUEST['select'],
                                            $_REQUEST['distinct'],
                                            $_REQUEST['filter'], $_REQUEST['order']);

          if(isset($_REQUEST['tree']) && $_REQUEST['tree'] == "true") {
            $items = array();
            $parents = array();
            foreach($rows as $item) {
              $items[$item['id']] = $item;
              $parents[$item['parent']][] = $item['id'];
            }
            $ret['items'] = $items;
            $ret['parents'] = $parents;
          }
          else {
            $ret['items'] = $rows;
          }
        }
        exit(prefixJSON(2, json_encode($ret)));

        break;
      case 'getForeignKeyReferences':
        $class->id = $_REQUEST['nodeId'];
        $rows = $svc->$_REQUEST['method']($class);
        exit(prefixJSON(2,json_encode($rows)));
        break;
      default:
        break;
    }
  }
  catch (Exception $e) {
    // prefix json, pass a generic message.
    // if it contains sqlstate[23000] send a duplicate message.
    $errorMsg = 'server error';
    if(strpos($e->getMessage(), 'SQLSTATE[23000]') !== False) {
      $errorMsg = 'duplicate';
    }

    echo prefixJSON(1, json_encode($errorMsg));
    // rethrow the exception with a code that indicates to the global
    // exception handler not to send the oops output.

    if(method_exists($e, 'displayOutputOff')) {
      $e->displayOutputOff();
      throw $e;
    }
    else {
      throw new ISLE\Exception($e->getMessage(), $e->getCode(), $e, false);
    }
  }

  function prefixJSON($status, $jsonStr) {
    switch($status) {
      case 1:
        $statusTxt = 'error';
        break;
      case 2:
        $statusTxt = 'success';
        break;
      default:
        $statusTxt = "'Unknown=" . $status . "'";
        break;
    }

    //return 'while(1);{"result":{"status":"' . $statusTxt . '", "value":' . $jsonStr . '}}';
    return '{"result":{"status":"' . $statusTxt . '", "value":' . $jsonStr . '}}';
  }

  function getVersions($u, $version = null) {
    $rows = $_SESSION['versions'];
    if(!empty($version)) {
      foreach($rows as $item) {
        if($item['version'] == $version) {
          $rows = array($item);
          break;
        }
      }
    }
    return $rows;
  }
?>
