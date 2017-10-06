<?php if(!isset($idsuffix)) {
  $idsuffix = '';
} ?>

<div class="modal hide" id="modalDialog<?php echo $idsuffix; ?>" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm<?php echo $idsuffix; ?>" id="modalForm<?php echo $idsuffix; ?>" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['manufacturerForm']['id']; ?>" id="<?php echo $fieldNames['manufacturerForm']['id']; ?>" value="" />
    <div class="modal-body">
      <div class="formItem first">
        <label for="<?php echo $fieldNames['manufacturerForm']['name']; ?>">Name</label>
        <input type="text" name="<?php echo $fieldNames['manufacturerForm']['name']; ?>" id="<?php echo $fieldNames['manufacturerForm']['name']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['manufacturerForm']['name']; ?>" class="err">&nbsp;</span>
      </div>
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['manufacturerForm']['url']; ?>">URL</label>
        <input type="text" name="<?php echo $fieldNames['manufacturerForm']['url']; ?>" id="<?php echo $fieldNames['manufacturerForm']['url']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['manufacturerForm']['url']; ?>" class="err">&nbsp;</span>
      </div>
    </div>
    <div class="modal-footer">
    </div>
  </form>
</div>