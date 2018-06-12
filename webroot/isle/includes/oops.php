<?php
  session_start();
  //use ISLE\Secrets;
  date_default_timezone_set("America/Los_Angeles");
  define('SERVER_INSTANCE', 'dev');
  define('SERVER_WEBROOT', '');
  require_once realpath(__DIR__ . '/classes/Service.php');
  $rootdir = SERVER_WEBROOT . '/' . ISLE\Service::getInstanceName() . '/';
  $stylesPath = 'cdn/styles/';
  if ( defined( 'SERVER_INSTANCE' ) && SERVER_INSTANCE == 'dev' ) { // dev
    $stylesPath = 'cdn/styles/css-dev/';
  }
  
  $csrfToken = base64_encode(hash("sha256", session_id()));
  
  function auto_version2($file)
  {
    $filepath = realpath(__DIR__ . '/../' . $file);
    if(strpos($filepath, '/') !== 0 || !file_exists($filepath))
      return $GLOBALS['rootdir'] . $file;

    $mtime = filemtime($filepath);
    return $GLOBALS['rootdir'] . preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
  }
?>
<!DOCTYPE html>
<html data-page="oops" lang="en">
<head>
<title><?php echo ISLE\Service::getInstanceName(); ?> - <?= $oopsTitle ?></title>
    <link rel="icon" href="<?= $rootdir ?>favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?= $rootdir ?>favicon.ico" type="image/x-icon" />
    
    <!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?php echo auto_version2($stylesPath . 'main_ie8.css'); ?>" /><![endif]-->
    <!--[if (lt IE 8)|(gt IE 8)]><!--><link rel="stylesheet" type="text/css" href="<?php echo auto_version2($stylesPath . 'main.css'); ?>" /><!--<![endif]-->

    <!--keep footer at the bottom of short length pages. IE6 fix.-->
    <!--[if lt IE 7]><style type="text/css">div#container {height:100%;}</style><![endif]-->

</head>
<body>
    <span class="hide" id="csrfToken"><?= $csrfToken ?></span>
    <div id="container">
    <div id="top">
      <div id="header">
		  <!--tabindex on anchor tags is needed for submenus to function in safari and chrome.-->
        <h1>ISLE</h1></div><!--end header-->
    </div><!--end top-->
    <div id="middle">
      <div id="body">
        <div id="bodyContent">
          <!-- Application messages. -->
          <?= $oopsMsg ?>
          
          <?php 
            $fieldNames['feedbackForm']['type'] = "selType";
            $fieldNames['feedbackForm']['description'] = "txtaDescription";
            $fieldNames['feedbackForm']['steps'] = "txtaSteps";
            $fieldNames['feedbackForm']['attachment'] = "filAttachment";

            require_once 'views/feedbackDialog.php';
          ?>
          
        </div> <!--end div#bodyContent-->
      </div> <!--end div#body-->
    </div> <!--end div#middle-->
    <div id="bottom">
      <div id="footer">
        <div id="footerLeftCont"><div id="footerLeft"><a class="feedbackLink" href="#">Got Feedback?</a></div></div>
        <div id="footerRight"><a href="<?= $rootdir ?>../wiki"><span><img src="<?= $rootdir ?>cdn/images/power.jpg" style="width:4.909em; height:2.455em;" alt="small green light image" /></span><!-- config-todo: -->Powered by My Team</a></div>
      </div>
    </div>
  </div><!--end div#container-->
<?php
  //Production scripts. First optimize them using node r.js. The optimized file contains all the js in one minified file to reduce http requests.
  $scriptsPath = 'cdn/scripts/';
  $baseUrl = $rootdir . rtrim($scriptsPath, '/');
  $appDir = 'app';
  if ( defined( 'SERVER_INSTANCE' ) && SERVER_INSTANCE == 'dev' ) {
    //Development scripts. This downloads all the unminified javascript files to the browser individually.
    $scriptsPath = 'cdn/scripts-dev/src/';
    $baseUrl = $rootdir . $scriptsPath . 'lib';
    $appDir = '../app';
  }
?>
  
<script type="text/javascript" language="javascript">
  var ISLE_VIEWER = <?= ISLE\Models\Role::VIEWER ?>;
  var ISLE_USER = <?= ISLE\Models\Role::USER ?>;
  var ISLE_CONTRIBUTOR = <?= ISLE\Models\Role::CONTRIBUTOR ?>;
  var ISLE_ADMIN = <?= ISLE\Models\Role::ADMIN ?>;
  var SERVER_ROOTDIR = "<?= $rootdir ?>";
  
  var configOpts = {
    baseUrl: '<?= $baseUrl ?>',
    paths: {
      app: '<?= $appDir ?>',
      jquery: 'jquery-1.7.2'
    },
    shim: {
      'bootstrap-dropdown': ['jquery'],
      'bootstrap-modal': ['jquery'],
      'bootstrap-alert': ['jquery'],
      'jquery.trap.min': ['jquery'],
      'jquery.dateFormat-1.0': ['jquery'],
      'jquery-ui-1.8.23.custom.min': ['jquery'],
      'jquery.combobox': ['jquery', 'jquery-ui-1.8.23.custom.min'],
      'tag-it': ['jquery', 'jquery-ui-1.8.23.custom.min']
    }
  };
</script>
<script data-main="<?php echo rtrim(auto_version2($scriptsPath . 'main.js'), '.js'); ?>" src="<?php echo auto_version2($scriptsPath . 'require.js'); ?>" type="text/javascript" language="javascript"></script>

</body>
</html>
