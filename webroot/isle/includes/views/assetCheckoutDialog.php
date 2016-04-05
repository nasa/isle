<div class="modal hide" id="modalDialog" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle">Check-out Asset</h3>
  </div>
  <form name="modalForm" id="modalForm" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['checkoutForm']['asset']; ?>" id="<?php echo $fieldNames['checkoutForm']['asset']; ?>" value="" />
  <div class="modal-body">
    
      <div class="formItem first<?php if(isset($errors['location'])){ echo ' inError'; } ?>">
        <label for="<?php echo $fieldNames['checkoutForm']['location']; ?>">Location</label>
        <select name="<?php echo $fieldNames['checkoutForm']['location']; ?>" id="<?php echo $fieldNames['checkoutForm']['location']; ?>">
          <option value="">-- Select a location</option>
          <!-- populate with data from database -->
          <?php
            foreach($locations as $location) {
              echo '<option value="' . $location['id'] . '">' . htmlspecialchars($location['center']) . ' ' . htmlspecialchars($location['bldg']) . '-' . htmlspecialchars($location['room']) . '</option>';
            }
          ?>
        </select>
        <span id="msg-<?php echo $fieldNames['checkoutForm']['location']; ?>" class="err"><?php if(isset($errors['location'])){ echo htmlspecialchars($errors['location']); } else { echo '&nbsp;'; } ?></span>
        <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
        <br /><button type="button" name="addLocationBtn" value="add" id="addLocationBtn" class="btn btn-mini"><i class="icon-plus"></i> Add Location</button>
        <?php } ?>
      </div>
    
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['checkoutForm']['purpose']; ?>">Purpose</label>
        <input type="text" name="<?php echo $fieldNames['checkoutForm']['purpose']; ?>" id="<?php echo $fieldNames['checkoutForm']['purpose']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['checkoutForm']['purpose']; ?>" class="err">&nbsp;</span>
      </div>
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['checkoutForm']['finish']; ?>">Est. Finish Date</label>
        <input type="text" name="<?php echo $fieldNames['checkoutForm']['finish']; ?>" id="<?php echo $fieldNames['checkoutForm']['finish']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['checkoutForm']['finish']; ?>" class="err">&nbsp;</span>
      </div>
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['checkoutForm']['notes']; ?>">Notes</label>
        <textarea name="<?php echo $fieldNames['checkoutForm']['notes']; ?>" id="<?php echo $fieldNames['checkoutForm']['notes']; ?>" class="width400 height6em"></textarea>
        <span id="msg-<?php echo $fieldNames['checkoutForm']['notes']; ?>" class="err">&nbsp;</span>
      </div>
  </div>
  <div class="modal-footer">
    <button type="submit" name="submit" value="Check-out" id="checkoutBtn" class="btn btn-primary">Check-out</button>
    <button type="button" name="cancel" value="Cancel" id="cancelBtn" class="btn">Cancel</button>
  </div>
  </form>
</div>

<?php 
  if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) {
    $idsuffix = "Location";
    require_once 'locationDialog.php';
  }
?>