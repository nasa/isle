<?php
  session_start();
  require_once 'includes/error.php';
  //require_once 'includes/classes/ActiveDirectory.php';
  use ISLE\ActiveDirectory;
  use ISLE\Secrets;

  $csrfToken = base64_encode(hash("sha256", session_id()));

  spl_autoload_register(function($class) {
    $class = str_replace('ISLE\\', '', $class);
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'classes' .
                 DIRECTORY_SEPARATOR .
                 str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
  });

  $svc = new ISLE\Service();
  $just_logged_out = False;
  if (isset($_GET["logout"]))
  {
    echo "<br/>Logging out " . $_SESSION["user"] . "<br/>";
    $_SESSION['message']['type'] = 'alert-error';
    $_SESSION['message']['text'] = 'LOGGED OUT';
    unset($_SESSION["user"]);
    $u = array('role' => ISLE\Models\Role::DISABLED);
    $just_logged_out = True;
  }

  $just_logged_in = False;
  if (!isset($_SESSION["user"]) and isset($_POST["username"]) and
      isset($_POST["password"])) {
    if ($_POST["username"] == Secrets::ADMIN_USER) {
      if ($_POST["password"] == Secrets::ADMIN_PASSWORD) {
        $_SESSION["user"] = Secrets::ADMIN_UID;
        unset($_POST["password"]);	// *** NEED TO FLAG NO NEW LOGIN.
        $just_logged_in = True;
      } else {
        login_error('Thou shalt not hack the ' . Secrets::ADMIN_USER . ' account.');
        unset($_POST["password"]);	// *** NEED TO FLAG NO NEW LOGIN.
        exit();
      }
    } else {				// Logging into non-admin account:
      try {
        $_SESSION["user"] = ISLE\ActiveDirectory::authenticate_user($_POST["username"],
                                                                    $_POST["password"]);
        unset($_POST["password"]);	// *** NEED TO FLAG NO NEW LOGIN.
        $just_logged_in = True;
      } catch (Exception $e) {
        login_error('Incorrect user name or password.');
        //echo '<br/>' . $e . '<br/>';
        unset($_POST["password"]);	// *** NOT SURE IF POST IS CLEARED EVERY
        exit();				// *** TIME. NEED TO FLAG NO NEW LOGIN.
      }
    }
  }

  $user = $_SESSION["user"];
  if (isset($user) and $user != 0) {
    $userClass = new ISLE\Models\User();
    $filter['cols'][0]['col'] = 'uid';
    $filter['cols'][0]['val'] = $user;
    // Get user from db... EVERY FREAKIN' TIME?!
    $u = $svc->getAll($userClass, null, null, null, null, $filter);
    if (count($u) == 0 or $u[0]['role'] == ISLE\Models\Role::DISABLED) {
      login_error('User ' . $u[0]["name"] . ' is not authorized for access.');
      unset($_SESSION["user"]);
      unset($u);
      exit();
    } else {
      $u = $u[0];
    }
  }

  if ($just_logged_in) {
    $just_logged_in = False;
    header("Location: " . $rootdir . "assets");
    exit();
  } elseif ($just_logged_out) {
    $just_logged_out = False;
    header("Location: " . $rootdir . "login");
    exit();
  }

  function login_error($msg = null) 
  {
    if ($msg == null) {
      $msg = 'Incorrect user name or password.';
    }
    $_SESSION['message']['type'] = 'alert-error';
    $_SESSION['message']['text'] = $msg;
    header("Location: " . $rootdir . "login");
    //echo "<b>IN login_error($msg)!!!!!!!</b><br/>";
  }
?>
