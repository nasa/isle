<?php
    require_once 'includes/config.php';
    use ISLE\Secrets;
    use ISLE\ActiveDirectory;

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

    // Setup an array or structure that contains the field names
    // so you only have one place to update if you want to change them.
    $fieldNames['id'] = "id";
    $fieldNames['uid'] = "selUID";
    $fieldNames['name'] = "hidName";
    $fieldNames['email'] = "hidEmail";
    $fieldNames['role'] = "selRole";

    // config-todo: set $employees to an array of employees.
    $employees = array();
    $employees[] = array( 'FULL_NAME'      => Secrets::ADMIN_USER,
                          'EMPLOYEENUMBER' => Secrets::ADMIN_UID,
                          'PRIMARYEMAIL'   => Secrets::ADMIN_EMAIL );

    // Attributes to return.
    $ldap_attrs = array( "samaccountname", "samaccounttype", "useraccountcontrol",
                         "mail", Secrets::LDAP_UID_ATTR );
    // Search for an account that is a normal user
    // AND (is not disabled or password is not required).
    $ldap_expr = ActiveDirectory::user_query_string('*', Secrets::EXTRA_ACCT_FILTER);

    // Establish connection to server:
    $ldap = new ActiveDirectory();

    echo "<p>Search=${ldap_expr}\n";
    $info = $ldap->search($ldap_expr, $ldap_attrs);

    //var_dump($info);
    //echo "<p>The number of entries returned is " . $info['count'] . "</p>";
    for ($i = 0; $i < $info['count']; $i++) {
      // Look for your user account in this pile of junk:
      try {
        if (ActiveDirectory::allowed_account($info[$i]["dn"])) {
          if (Secrets::USE_SID) {			// Convert Windows SID:
            $emp_num = ActiveDirectory::SID_to_userid($info[$i][Secrets::LDAP_UID_ATTR][0]);
          } else {					// Else assume Posix user ID:
            $emp_num = intval($info[$i][Secrets::LDAP_UID_ATTR][0]);
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

    $userClass = new ISLE\Models\User();
    // Get users from db:
    //$users = $svc->getAll($userClass, null, null, null, null, null, null);
    $options = array("sort" => array("name" => "ASC"));
    $users = $svc->getAll($userClass, null, null,
                          array("uid", "name", "email", "role"),
                          null, null, $options);
    //echo "<p>DUMPING USER DATABASE:</p>";
    //var_dump($users);

/*
    $employees = array_filter($employees,
                              function ($employee) use ($users) {
                                foreach ($users as $usr) {
                                  if ($employee['EMPLOYEENUMBER'] == intval($usr["uid"])) {
                                    return False;
                                  }
                                }
                                return True;
                              });
*/

    usort($employees, function ($emp1, $emp2) {
                        return strcasecmp($emp1['FULL_NAME'], $emp2['FULL_NAME']);
                      });

    //echo "<p>DUMPING AD USERS:</p>";
    //var_dump($employees);

    // do something like search or update the directory
    // and display the results

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
