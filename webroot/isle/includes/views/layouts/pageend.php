        <?php 
          $fieldNames['feedbackForm']['type'] = "selType";
          $fieldNames['feedbackForm']['description'] = "txtaDescription";
          $fieldNames['feedbackForm']['steps'] = "txtaSteps";
          $fieldNames['feedbackForm']['attachment'] = "filBugAttachment";
        
          require_once 'includes/views/feedbackDialog.php';
        ?>

        </div> <!--end div#bodyContent-->
      </div> <!--end div#body-->
    </div> <!--end div#middle-->
    <div id="bottom">
      <div id="footer">
        <div id="footerLeftCont"><div id="footerLeft"><div class="floatLeft marginLR7">Version: <a href="<?php echo $rootdir; ?>versionhistory/<?php echo $_SESSION['versions'][0]['version'];?>"><?php echo $_SESSION['versions'][0]['version'];?></a></div><a class="feedbackLink" href="#">Got Feedback?</a></div></div>
        <div id="footerRight"><a href="<?php echo $rootdir; ?>"><span><img src="<?php echo $rootdir; ?>cdn/images/power.jpg" style="width:4.909em; height:2.455em;" alt="small green light image" /></span><!-- config-todo: -->Powered by My Team</a></div>
      </div>
    </div>
  </div><!--end div#container-->
  <?php
    if(isset($tmpl_javascripts)) {
      echo $tmpl_javascripts;
    }
  ?>
</body>
</html>