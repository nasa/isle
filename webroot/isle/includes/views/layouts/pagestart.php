<!DOCTYPE html>
<html lang="en" data-user="<?php echo $u['uid']; ?>" data-role="<?php echo $u['role']; ?>">
<head>
<?php
/*Grab all the styles for this page and include them in link tags here.
  If production grab the minified versions*/
echo $tmpl_headcontent;
?>
</head>
<?php flush();/*Allows the browser to start getting content while the server
                is still loading the rest of the page.
                http://developer.yahoo.com/performance/rules.html#page-nav*/ ?>
<body>
    <span class="hide" id="csrfToken"><?php echo $csrfToken ?></span>
    <div id="container">
    <div id="top">
      <div id="header">
        <span class="logout floatRight">
          <?php
            // $_SESSION["user"] contains the user ID number.
            if (isset($u['name']) and $u['name'] != '') {
              echo("Welcome, " . $u['name']);
              echo('&nbsp;&nbsp;<a id="logout" href="javascript:void(0);">Logout</a>');
              echo('<br />');
              echo('<a href="' . $rootdir . 'assets?item=user&value=' .
                   $u['uid'] . '">My Assets</a>');
            } else {
              echo('<a href="' . $rootdir . 'login" class="login-window">Login</a>');
            }
          ?>
        </span>
        <!-- tabindex on anchor tags is needed for submenus to function in safari
             and chrome. -->
        <h1>ISLE: <?php echo $svc->getInstanceName(); ?></h1>
        <?php if (!isset($hidenav) or !$hidenav) { ?>
        <div id="nav">
          <ul>
            <li>
              <a href="<?php echo $rootdir; ?>assets">Assets</a>
            </li>
            <li>
              <a href="<?php echo $rootdir; ?>manufacturers">Manufacturers</a>
            </li>
            <li>
              <a href="<?php echo $rootdir; ?>locations">Locations</a>
            </li>
            <li>
              <a href="<?php echo $rootdir; ?>categories">Categories</a>
            </li>
            <?php
              if ($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
                <li><a href="<?php echo $rootdir; ?>attributes">Attributes</a></li>
                <li><a href="<?php echo $rootdir; ?>relations">Relations</a></li>
            <?php
              } //endif
              if ($u['role'] >= ISLE\Models\Role::ADMIN) { ?>
                <li><a href="<?php echo $rootdir; ?>users">Users</a></li>
                <li><a href="<?php echo $rootdir; ?>logs">Logs</a></li>
            <?php } ?>
          </ul>
        </div><?php }//endif ?><!--end nav-->
      </div><!--end header-->
    </div><!--end top-->
    <div id="middle">
      <div id="body">
        <div id="bodyContent">
          <!-- Application messages. -->
          <?php if(!isset($dontShowMsg) && isset($_SESSION['message'])) { ?>
          <div id="userMessage" role="alert"
               aria-label="<?php echo htmlspecialchars($_SESSION['message']['text']); ?>"
               class="fade in alert<?php echo ' ' . $_SESSION['message']['type']; ?>">
            <a class="close" href="#" role="button" aria-label="Close"
               data-dismiss="alert">&times;
            </a><?php echo htmlspecialchars($_SESSION['message']['text']); ?>
          </div>
          <?php unset($_SESSION['message']); } ?>

