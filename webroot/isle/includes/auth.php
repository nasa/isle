<?php
  session_start();
  require_once 'includes/error.php';

  $csrfToken = base64_encode(hash("sha256", session_id()));

  spl_autoload_register(function($class) {
    $class = str_replace('ISLE\\', '', $class);
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'classes' .
                 DIRECTORY_SEPARATOR .
                 str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
  });

  $svc = new ISLE\Service();
  if (isset($_GET["logout"]))
  {
    unset($_SESSION["user"]);
  }
  if (!isset($_SESSION["user"]))
  {
    //Store as power/User object in session.
    try
    {
      // config-todo: replace 111111111 with whatever ID the auth mechanism you
      // use returns when successful.
      $_SESSION["user"] = 1;
    } catch (Exception $e)
    {
      echo $e->getMessage();
    }
  }

  $user = $_SESSION["user"];

  $userClass = new ISLE\Models\User();
  $filter['cols'][0]['col'] = 'uid';
  $filter['cols'][0]['val'] = $user;
  //get user from db.
  $u = $svc->getAll($userClass, null, null, null, null, $filter);
  if(count($u) == 0 || $u[0]['role'] == ISLE\Models\Role::DISABLED) {
    echo 'User <i>'.$user.'</i> is not authorized.';
    exit;
  }
  else {
    $u = $u[0];
  }
?>
