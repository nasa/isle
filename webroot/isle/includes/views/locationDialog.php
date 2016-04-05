<?php if(!isset($idsuffix)) {
  $idsuffix = '';
} ?>

<div class="modal hide" id="modalDialog<?php echo $idsuffix; ?>" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm<?php echo $idsuffix; ?>" id="modalForm<?php echo $idsuffix; ?>" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['locationForm']['id']; ?>" id="<?php echo $fieldNames['locationForm']['id']; ?>" value="" />
  <div class="modal-body">
      <div class="formItem first">
        <label for="<?php echo $fieldNames['locationForm']['center']; ?>">Center</label>
        <input type="text" name="<?php echo $fieldNames['locationForm']['center']; ?>" id="<?php echo $fieldNames['locationForm']['center']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['locationForm']['center']; ?>" class="err">&nbsp;</span>
      </div>
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['locationForm']['bldg']; ?>">Building</label>
        <input type="text" name="<?php echo $fieldNames['locationForm']['bldg']; ?>" id="<?php echo $fieldNames['locationForm']['bldg']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['locationForm']['bldg']; ?>" class="err">&nbsp;</span>
      </div>
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['locationForm']['room']; ?>">Room</label>
        <input type="text" name="<?php echo $fieldNames['locationForm']['room']; ?>" id="<?php echo $fieldNames['locationForm']['room']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['locationForm']['room']; ?>" class="err">&nbsp;</span>
      </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>