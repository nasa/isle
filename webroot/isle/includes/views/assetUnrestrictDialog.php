<div class="modal hide" id="modalDialog4" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle">Unrestrict Asset</h3>
  </div>
  <form name="modalForm" id="modalForm4" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['unRestrictForm']['asset']; ?>" value="" />
  <div class="modal-body">
    <div class="formItem first">
      <label for="<?php echo $fieldNames['unRestrictForm']['notes']; ?>">Notes</label>
      <textarea name="<?php echo $fieldNames['unRestrictForm']['notes']; ?>" id="<?php echo $fieldNames['unRestrictForm']['notes']; ?>" class="width400 height6em"></textarea>
      <span id="msg-<?php echo $fieldNames['unRestrictForm']['notes']; ?>" class="err">&nbsp;</span>
    </div>
  </div>
  <div class="modal-footer">
    <button type="submit" name="submit" value="Unrestrict" id="unRestrictBtn" class="btn btn-primary">Unrestrict</button>
    <button type="button" name="cancel" value="Cancel" id="cancelBtn" class="btn">Cancel</button>
  </div>
  </form>
</div>