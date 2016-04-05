<div class="listRightCont">
  <div class="listRight">
    <?php if(isset($_SESSION['message'])) { $hasMessage = true; ?>
    <div id="userMessage" role="alert" aria-label="<?php echo htmlspecialchars($_SESSION['message']['text']); ?>" class="fade in alert<?php echo ' ' . $_SESSION['message']['type']; ?>"><a class="close" href="#" role="button" aria-label="Close" data-dismiss="alert">&times;</a><?php echo htmlspecialchars($_SESSION['message']['text']); ?></div>
    <?php unset($_SESSION['message']); } ?>
    <div>
    <form class="asset-actions">
      <button type="button" name="checkOut" value="checkOut" id="checkOutMultBtn" class="btn btn-mini hide"><span class="inline-block badge"></span> Check-out</button>
      <button type="button" name="checkIn" value="checkIn" id="checkInMultBtn" class="btn btn-mini hide"><span class="inline-block badge"></span> Check-in</button>
      <div id="" class="alert alert-error hide" role="alert" aria-label="No actions available for selected items.">No actions available for items selected.</div>
    </form>
    <div class="listControls" id="listNav">
      <!-- floats must come first for ie7 -->
      <b id="firstItem"></b>-<b id="lastItem"></b> of <b id="totalItems"></b>
      <div class="btn-group">
      <a id="prevPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Previous Page"><i class="icon-chevron-left"></i></a>
      <a id="nextPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Next Page"><i class="icon-chevron-right"></i></a>
      </div>
    </div>
    <form class="form-search">
      <div class="input-append">
        <input type="text" placeholder="filter by keyword" class="span2 search-query width250" id="searchBox" aria-label="Search" />
        <button type="submit" class="btn" id="searchBtn" aria-label="Search"><i class="icon-search" alt="search"></i></button>
      </div>
    </form>
    
    </div>
    <div class="scroll-pane width100<?php if(isset($hasMessage)) { echo ' has-message'; unset($hasMessage); } ?>">
    <table id="nodeTable" class="table table-striped table-clickable">
    </table>
    </div>
  </div>
</div>
<div class="listLeft center">
  <a <?php if($u['role'] <= ISLE\Models\Role::USER) { echo 'style="visibility:hidden" '; } ?>href="<?php echo $rootdir ?>assets/new" id="addItemBtn" class="btn btn-primary" autofocus="autofocus"><i class="icon-plus icon-white"></i> Add Asset</a>
  <ul id="VerColMenu" class="marginT10">
    <li><a id="showAll" class="fontsize floatRight marginT6" href="<?php echo $rootdir ?>assets">show all</a><h4 class="inline-block">Categories</h4></li>
    
    <?php
      $listHTML = '';
      if(count($catItems) > 0) {
        recurse("", -1, $catItems, $catParents, $listHTML);
      }
      else {
        echo '<li>No categories found.</li>';
      }
      
      function recurse($id, $level, $catItems, $catParents, &$listHTML) {
        
        if(array_key_exists($id, $catParents) && is_array($catParents[$id])) {
          if($id != "") {
            $cur = $catItems[$id];
            $listHTML .= '<li><span class="item"><span class="expander" tabindex="0" role="button">+</span><span class="selector" tabindex="0" role="button" data-id="' . $cur['id'] . '">' . htmlspecialchars($cur['name']) . '</span></span>';
          }
          $j = 0;
          foreach($catParents[$id] as $value) {
            if($j == 0 && $id != "") {
              $listHTML .= '<ul>';
              $j++;
            }
            recurse($value, $level + 1, $catItems, $catParents, $listHTML);
          }
          if($id != "") {
            $listHTML .= '</ul>';
          }
        }
        else {
          $cur = $catItems[$id];
          $listHTML .= '<li><span class="item"><span class="selector" tabindex="0" role="button" data-id="' . $cur['id'] . '">' . htmlspecialchars($cur['name']) . '</span></span></li>';
        }
        return;
      }
      echo $listHTML;
    ?>
  </ul>
  
</div>
<?php
  if($u['role'] > ISLE\Models\Role::VIEWER) {
    require_once 'assetCheckoutDialog.php';
  }
  require_once 'assetCheckinDialog.php';
?>