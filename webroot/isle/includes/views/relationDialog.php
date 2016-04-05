<?php if(!isset($idsuffix)) {
  $idsuffix = '';
} ?>

<div class="modal hide" id="modalDialog<?php echo $idsuffix; ?>" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm<?php echo $idsuffix; ?>" id="modalForm<?php echo $idsuffix; ?>" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['relationForm']['id']; ?>" id="<?php echo $fieldNames['relationForm']['id']; ?>" value="" />
  <div class="modal-body">
      <div class="formItem first">
        <label for="<?php echo $fieldNames['relationForm']['name']; ?>">Name</label>
        <input type="text" name="<?php echo $fieldNames['relationForm']['name']; ?>" id="<?php echo $fieldNames['relationForm']['name']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['relationForm']['name']; ?>" class="err">&nbsp;</span>
      </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>