<?php
    require_once 'includes/auth.php';

    $rootdir = SERVER_WEBROOT . '/' . ISLE\Service::getInstanceName() . '/';
    
    $rows = array();
    
    $desc = "
<p>ISLE supports multiple lab inventories under the same application. Each instance stores its data in separate database tables, and logs and uploads are maintained in separate directories.</p>
<p>Added logs menu to top nav for administrators.</p>
<p>Check-out button is no longer visible on the asset detail page when an asset is checked out to someone else.</p>
<p>Added a help link to the header.</p>
<p>Added image modifed timestamp to asset models table to add as url param to image to break cache when image is updated.</p>
<p>Added auto versioning to css and js files so that the cache is broken automatically when the files are updated.</p>
<p>Bug fixes.</p>
    ";
    $rows[] = array('version' => '1.3.0', 'revision' => '413', 'date' => '2014-07-09', 'description' => $desc);
    
    $desc = "
<p>Bug fix.</p>
    ";
    $rows[] = array('version' => '1.2.2', 'revision' => '373', 'date' => '2013-08-27', 'description' => $desc);
    
    $desc = "
<p>Minor fixes.</p>
    ";
    $rows[] = array('version' => '1.2.1', 'revision' => '371', 'date' => '2013-06-28', 'description' => $desc);
    
    $desc = "
<p>Changed roles and permissions.</p>
<p>Minor fixes.</p>
    ";
    $rows[] = array('version' => '1.2', 'revision' => '359', 'date' => '2013-05-16', 'description' => $desc);
    
    $desc = "
<p>You can now see what assets you have checked out by clicking the link under the Welcome text in the upper-right corner.</p>
<p>Minor fixes.</p>
    ";
    $rows[] = array('version' => '1.1', 'revision' => '347', 'date' => '2013-05-08', 'description' => $desc);
    
    $desc = "
<p>The initial release includes the following features:</p><p>
Assets and their models can be added/edited/deleted.<br />
Assets can be checked-out, checked-in, and restricted.<br />
Multiple assets can be checked-out/checked-in at a time.<br />
Asset models can be assigned custom attributes, relations with other models, and categories.<br />
Assets and asset models can have .pdf attachments.<br />
Asset models can have a product image.<br />
Locations, Manufacturers, Custom Attributes and Attribute Types, Relations, and Categories can be added/edited/deleted.<br />
Assets can be located by category, or by searching over the following fields: Serial No., Model No., Model Description, Series, Manufacturer.<br />
An asset's transaction history can be viewed.<br />
Manufacturers, locations, and relations can be added right from the forms they are used on.<br />
Users can be added/edited/deleted and assigned one of the following roles:<br />
&nbsp;&nbsp;&nbsp;User - View only.<br />
&nbsp;&nbsp;&nbsp;Contributor - User + add/edit/delete.<br />
&nbsp;&nbsp;&nbsp;Administrator - Contributor + add/edit/delete users and assign roles.</p>
    ";
    $rows[] = array('version' => '1.0', 'revision' => '330', 'date' => '2013-05-02', 'description' => $desc);
    
    $_SESSION['versions'] = $rows;
    
    $stylesPath = 'cdn/styles/';
    if ( defined( 'SERVER_INSTANCE' ) && SERVER_INSTANCE == 'dev' ) { // dev
      $stylesPath = 'cdn/styles/css-dev/';
    }
    $layoutsPath = 'includes/views/layouts/';
    $viewsPath = 'includes/views/';
    
    ob_start();
?>
    <link rel="icon" href="<?php echo $rootdir; ?>favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $rootdir; ?>favicon.ico" type="image/x-icon" />

    <!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?php echo auto_version($stylesPath . 'main_ie8.css'); ?>" /><![endif]-->
    <!--[if (lt IE 8)|(gt IE 8)]><!--><link rel="stylesheet" type="text/css" href="<?php echo auto_version($stylesPath . 'main.css'); ?>" /><!--<![endif]-->

    <!--keep footer at the bottom of short length pages. IE6 fix.-->
    <!--[if lt IE 7]><style type="text/css">div#container {height:100%;}</style><![endif]-->
<?php
    $tmpl_headcontent_main = ob_get_clean();
    
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
    
    ob_start();
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
    urlArgs: 'bust=v2',
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
<script data-main="<?php echo rtrim(auto_version($scriptsPath . 'main.js'), '.js'); ?>" src="<?php echo auto_version($scriptsPath . 'require.js'); ?>" type="text/javascript" language="javascript"></script>

<?php
    $tmpl_javascripts_main = ob_get_clean();
    
    function javascript_escape($str) {
      $new_str = '';

      $str_len = strlen($str);
      for($i = 0; $i < $str_len; $i++) {
        $new_str .= '\\x' . dechex(ord(substr($str, $i, 1)));
      }

      return $new_str;
    }
    
    function savePOST() {
      $_SESSION['POST'] = $_POST;
    }
    
    function restorePOST() {
      $_POST = $_SESSION['POST'];
      unset($_SESSION['POST']);
    }
    
    /**
    *  Given a file, i.e. /css/base.css, replaces it with a string containing the
    *  file's mtime, i.e. /css/base.1221534296.css.
    *  
    *  @param $file  The file to be loaded.  Must be an absolute path (i.e.
    *                starting with slash).
    */
   function auto_version($file)
   {
     $filepath = realpath(__DIR__ . '/../' . $file);
     if(strpos($filepath, '/') !== 0 || !file_exists($filepath))
       return $GLOBALS['rootdir'] . $file;

     $mtime = filemtime($filepath);
     return $GLOBALS['rootdir'] . preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
   }
?>
