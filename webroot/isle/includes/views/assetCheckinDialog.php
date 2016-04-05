<div class="modal hide" id="modalDialog2" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle">Check-in Asset</h3>
  </div>
  <form name="modalForm2" id="modalForm2" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['checkinForm']['asset']; ?>" value="" />
  <div class="modal-body">
    <div class="formItem first">
      <label for="<?php echo $fieldNames['checkinForm']['notes']; ?>">Notes</label>
      <textarea name="<?php echo $fieldNames['checkinForm']['notes']; ?>" id="<?php echo $fieldNames['checkinForm']['notes']; ?>" class="width400 height6em"></textarea>
      <span id="msg-<?php echo $fieldNames['checkinForm']['notes']; ?>" class="err">&nbsp;</span>
    </div>
    <div class="formItem newline">
      <label for="chkConfirmReturned"><input type="checkbox" name="chkConfirmReturned" value="Yes" id="chkConfirmReturned" />I returned the item to <span id="retLocation"></span><span id="msg-chkConfirmReturned" class="err">&nbsp;</span></label>
    </div>
  </div>
  <div class="modal-footer">
    <button type="submit" name="submit" value="Check-in" id="checkinBtn" class="btn btn-primary">Check-in</button>
    <button type="button" name="cancel" value="Cancel" id="cancelBtn2" class="btn">Cancel</button>
  </div>
  </form>
</div>