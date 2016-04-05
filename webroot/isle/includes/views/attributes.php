<div class="listRightCont">
  <div class="listRight">
    <div class="listControls" id="listNav">
      <!-- floats must come first for ie7 -->
      <b id="firstItem"></b>-<b id="lastItem"></b> of <b id="totalItems"></b>
      <div class="btn-group">
        <a id="prevPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Previous Page"><i class="icon-chevron-left"></i></a>
        <a id="nextPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Next Page"><i class="icon-chevron-right"></i></a>
      </div>
    </div>
    <div class="scroll-pane">
    <table id="nodeTable" class="table table-striped<?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { echo ' table-clickable'; } ?>">
    </table>
    </div>
  </div>
</div>
<div class="listLeft center">
  <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
  <button type="button" name="addItemBtn" value="add" id="addItemBtn" class="btn btn-primary" autofocus="autofocus"><i class="icon-plus icon-white"></i> Add Attribute</button>
  <?php } ?>
  <p><br/><a href="<?php echo $rootdir; ?>attributetypes">Manage Attribute Types</a></p>
</div>

<?php 
  if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) {
    require_once 'attributeDialog.php';
  }
?>