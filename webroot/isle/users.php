<?php
    require_once 'includes/config.php';
    require_once 'includes/secrets.php';
//phpinfo();
    require_once 'includes/classes/ActiveDirectory.php';


    if(isset($_GET['action'])) {
      throw new ISLE\Exception('Actions are not valid on this page.',
                               ISLE\Exception::FOUROFOUR);
    }
    
    if($u['role'] < ISLE\Models\Role::ADMIN) {
      $_SESSION['message']['type'] = 'alert-error';
      $_SESSION['message']['text'] = 'You do not have permission to do that.';
      header("Location: " . $rootdir . "assets");
      exit;
    }
    
    // setup an array or structure that contains the field names.
    // so you only have one place to update if you want to change them
    $fieldNames['id'] = "id";
    $fieldNames['uid'] = "selUID";
    $fieldNames['name'] = "hidName";
    $fieldNames['email'] = "hidEmail";
    $fieldNames['role'] = "selRole";
    
    // config-todo: set $employees to an array of employees.
    $employees = array( array( 'FULL_NAME' => "admin", 'EMPLOYEENUMBER' => 0,
                               'PRIMARYEMAIL' => "SPG-DevOps@cavium.com" ) );

    if (Secrets::USE_SID) {
      $uid_attr = "objectsid";		// Windows SID.
    } else {
      $uid_attr = "uidnumber";		// Assumes Posix user ID.
    }
    $ldap_attrs = array( "samaccountname", "samaccounttype", "useraccountcontrol",
                         "mail", $uid_attr );
    // Search for an account that is a normal user
    // AND (is not disabled or password is not required).
    $ldap_expr = "(&(sAMAccountType=" .
                     ActiveDirectory::SAM_NORMAL_USER_ACCOUNT . ")" .
                   "(!(objectCategory=computer))" .
                   "(userAccountControl:" .
                     ActiveDirectory::LDAP_MATCHING_RULE_BIT_OR .
                     ":=" . ActiveDirectory::NORMAL_ACCOUNT . ")" .
                   "(!(samAccountName=sa_*))(!(samAccountName=priv_*))" .
                   "(!(userAccountControl:" .
                       ActiveDirectory::LDAP_MATCHING_RULE_BIT_OR .
                       ":=" .
                       (ActiveDirectory::ACCOUNTDISABLE |
                        ActiveDirectory::PASSWD_NOTREQD |
                        ActiveDirectory::PASSWORD_EXPIRED) . ")))";

    // Establish connection to server:
    $ldap = new ActiveDirectory();

    echo "<p>Search=${ldap_expr}\n";
    $info = $ldap->search($ldap_expr, $ldap_attrs);

    //var_dump($info);
    echo "<p>The number of entries returned is " . $info['count'] . "</p>";
    for ($i = 0; $i < $info['count']; $i++) {
      // Look for your user account in this pile of junk:
      try {
        if (ActiveDirectory::allowed_account($info[$i]["dn"])) {
          if (Secrets::USE_SID) {
            $emp_num = ActiveDirectory::SID_to_userid($info[$i][$uid_attr][0])
          } else {
            $emp_num = intval($info[$i][$uid_attr][0]);	// Assumes Posix user ID.
          }
          $employees[]= array( 'FULL_NAME'      => $info[$i]["samaccountname"][0],
                               'EMPLOYEENUMBER' => $emp_num,
                               'PRIMARYEMAIL'   => $info[$i]["mail"][0] );
        }
      } catch (Exception $e) {
        echo '<p>Caught exception: ', $e->getMessage(), "</p>";
        var_dump($info[$i]);
      }

// IN USER DATABASE: uid, name, email, role
// IN employees: 'PRIMARYEMAIL', 'EMPLOYEENUMBER', 'FULL_NAME'
            //
/*            echo("<p> Name:" . $info[$i]["samaccountname"][0] .
                 ", Type:" .
                 ActiveDirectory::$SamaccounttypeValues[intval($info[$i]["samaccounttype"][0])] .
                 ", Control:" .
                 ActiveDirectory::print_UAC_flags($info[$i]["useraccountcontrol"][0]));
*/
    }
    var_dump($employees);
    // do something like search or update the directory
    // and display the results
    //ldap_close($ldap_conn);

// ob_end_flush();

    $modelClass = new ISLE\Models\Role();
    $order[0]['col'] = 'id';
    //get list of roles from db, so ui can load into dropdown.
    $roles = $svc->getAll($modelClass, null, null, null, null, null, $order);
    
    $tmpl_headcontent = "<title>" . ISLE\Service::getInstanceName() . " - Users</title>";
    $tmpl_headcontent .= $tmpl_headcontent_main;
    $tmpl_headcontent .= '<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list_ie7.css') . '" /><![endif]-->';
    $tmpl_headcontent .= '<!--[if gte IE 8]><!--><link rel="stylesheet" type="text/css" href="' . auto_version($stylesPath . 'list.css') . '" /><!--<![endif]-->';
    
    $tmpl_javascripts = $tmpl_javascripts_main;
    
    require_once $layoutsPath . 'pagestart.php';
    require_once $viewsPath . 'users.php';
    require_once $layoutsPath . 'pageend.php'; 
?>
