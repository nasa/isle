<div class="listRightCont">
  <div class="listRight">
    <div class="scroll-pane">
    <table id="nodeTable" class="table<?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { echo ' table-clickable'; } ?>">
    </table>
    </div>
  </div>
</div>
<div class="listLeft center">
  <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
  <button type="button" name="addItemBtn" value="add" id="addItemBtn" class="btn btn-primary" autofocus="autofocus"><i class="icon-plus icon-white"></i> Add Category</button>
  <?php } ?>
</div>

<?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
<div class="modal hide" id="modalDialog" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm" id="modalForm" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['id']; ?>" id="<?php echo $fieldNames['id']; ?>" value="" />
  <div class="modal-body">
      <div class="formItem first">
        <label for="<?php echo $fieldNames['name']; ?>">Name</label>
        <input type="text" name="<?php echo $fieldNames['name']; ?>" id="<?php echo $fieldNames['name']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['name']; ?>" class="err">&nbsp;</span>
      </div>
    
      <div class="formItem newline ui-widget">
        <label for="<?php echo $fieldNames['parent']; ?>">Parent</label>
        <select name="<?php echo $fieldNames['parent']; ?>" id="<?php echo $fieldNames['parent']; ?>"></select>
        <span id="msg-<?php echo $fieldNames['parent']; ?>" class="err">&nbsp;</span>
      </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>
<?php } ?>