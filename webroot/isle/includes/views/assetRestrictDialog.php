<div class="modal hide" id="modalDialog3" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle">Restrict Asset</h3>
  </div>
  <form name="modalForm" id="modalForm3" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['restrictForm']['asset']; ?>" value="" />
  <div class="modal-body">
    <div class="formItem first">
      <label for="<?php echo $fieldNames['restrictForm']['purpose']; ?>">Purpose</label>
      <input type="text" name="<?php echo $fieldNames['restrictForm']['purpose']; ?>" id="<?php echo $fieldNames['restrictForm']['purpose']; ?>" class="width250" />
      <span id="msg-<?php echo $fieldNames['restrictForm']['purpose']; ?>" class="err">&nbsp;</span>
    </div>
    <div class="formItem newline">
      <label for="<?php echo $fieldNames['restrictForm']['notes']; ?>">Notes</label>
      <textarea name="<?php echo $fieldNames['restrictForm']['notes']; ?>" id="<?php echo $fieldNames['restrictForm']['notes']; ?>" class="width400 height6em"></textarea>
      <span id="msg-<?php echo $fieldNames['restrictForm']['notes']; ?>" class="err">&nbsp;</span>
    </div>
  </div>
  <div class="modal-footer">
    <button type="submit" name="submit" value="Restrict" id="restrictBtn" class="btn btn-primary">Restrict</button>
    <button type="button" name="cancel" value="Cancel" id="cancelBtn" class="btn">Cancel</button>
  </div>
  </form>
</div>