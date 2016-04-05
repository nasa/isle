<h2><?php if($action == 'edit') { echo 'Asset #' . $itemToEdit; } else { echo 'Add Asset'; } ?></h2>

<form name="assetForm" id="assetForm" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrfToken" value="<?php echo $csrfToken ?>" />
  <input type="hidden" name="<?php echo $fieldNames['assetForm']['id']; ?>" id="<?php echo $fieldNames['assetForm']['id']; ?>" value="<?php if(isset($_POST[$fieldNames['assetForm']['id']])) { echo htmlspecialchars($_POST[$fieldNames['assetForm']['id']]); } ?>" />
  
  <div id="modelDiv" class="formItem first<?php if(isset($errors['model'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetForm']['model']; ?>">Model</label>
    <select class="bring-to-front" name="<?php echo $fieldNames['assetForm']['model']; ?>" id="<?php echo $fieldNames['assetForm']['model']; ?>" autofocus="autofocus"<?php if($u['role'] <= ISLE\Models\Role::USER) { echo ' disabled="disabled"'; } ?>>
      <option value="">-- Select a model</option>
      <!-- populate with data from database -->
      <?php
        foreach($models as $model) {
          $selected = '';
          if($selectedModel == $model['id']) {
            $selected = ' selected="selected"';
          }
          $seriesTxt = '';
          if(array_key_exists('series', $model) && $model['series'] != '') {
            $seriesTxt = ' ' . htmlspecialchars($model['series']);
          }
          echo '<option value="' . $model['id'] . '"' . $selected . '>' . htmlspecialchars($model['Manufacturer_name']) . $seriesTxt . ' ' . htmlspecialchars($model['model']) . '</option>';
        }
      ?>
    </select>
    <span id="msg-<?php echo $fieldNames['assetForm']['model']; ?>" class="err"><?php if(isset($errors['model'])){ echo htmlspecialchars($errors['model']); } else { echo '&nbsp;'; } ?></span><br />
    <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
    <button type="submit" name="addModelBtn" value="Add" id="addModelBtn" class="btn btn-mini"><i class="icon-plus"></i> Add Model</button> <button type="submit" name="editModelBtn" value="Edit" id="editModelBtn" class="btn btn-mini"><i class="icon-edit"></i> Edit Model</button>
    <?php } ?>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['location'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetForm']['location']; ?>">Home Location</label>
    <select name="<?php echo $fieldNames['assetForm']['location']; ?>" id="<?php echo $fieldNames['assetForm']['location']; ?>" data-location="<?php if(get_class($_SESSION['itemDetails']) != '__PHP_Incomplete_Class') { echo htmlspecialchars($_SESSION['itemDetails']->location); } ?>"<?php if($u['role'] <= ISLE\Models\Role::USER) { echo ' disabled="disabled"'; } ?>>
      <option value="">-- Select a location</option>
      <!-- populate with data from database -->
      <?php
        foreach($locations as $location) {
          $selected = '';
          if(isset($_POST[$fieldNames['assetForm']['location']]) && $_POST[$fieldNames['assetForm']['location']] == $location['id']) {
            $selected = ' selected="selected"';
          }
          echo '<option value="' . $location['id'] . '"' . $selected . '>' . htmlspecialchars($location['center']) . ' ' . htmlspecialchars($location['bldg']) . '-' . htmlspecialchars($location['room']) . '</option>';
        }
      ?>
    </select>
    <span id="msg-<?php echo $fieldNames['assetForm']['location']; ?>" class="err"><?php if(isset($errors['location'])){ echo htmlspecialchars($errors['location']); } else { echo '&nbsp;'; } ?></span>
    <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
    <br /><button type="button" name="addLocationBtn" value="add" id="addLocationBtn" class="btn btn-mini"><i class="icon-plus"></i> Add Location</button>
    <?php } ?>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['serial'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetForm']['serial']; ?>">Serial No.</label>
    <input type="text" name="<?php echo $fieldNames['assetForm']['serial']; ?>" id="<?php echo $fieldNames['assetForm']['serial']; ?>" class="width250" value="<?php if(isset($_POST[$fieldNames['assetForm']['serial']])) { echo htmlspecialchars($_POST[$fieldNames['assetForm']['serial']]); } ?>"<?php if($u['role'] <= ISLE\Models\Role::USER) { echo ' disabled="disabled"'; } ?> />
    <span id="msg-<?php echo $fieldNames['assetForm']['serial']; ?>" class="err"><?php if(isset($errors['serial'])){ echo htmlspecialchars($errors['serial']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['notes'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetForm']['notes']; ?>">Notes</label>
    <textarea name="<?php echo $fieldNames['assetForm']['notes']; ?>" id="<?php echo $fieldNames['assetForm']['notes']; ?>" class="width400 height6em"<?php if($u['role'] <= ISLE\Models\Role::USER) { echo ' disabled="disabled"'; } ?>><?php if(isset($_POST[$fieldNames['assetForm']['notes']])) { echo htmlspecialchars($_POST[$fieldNames['assetForm']['notes']]); } ?></textarea>
    <span id="msg-<?php echo $fieldNames['assetForm']['notes']; ?>" class="err"><?php if(isset($errors['notes'])){ echo htmlspecialchars($errors['notes']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['attachment'])){ echo ' inError'; } ?>">
    <label for="<?php echo $othFieldNames['attachment']; ?>">Attachments <span class="ro">(pdf)</span></label>
    <div class="floatLeft">
    <?php 
    //put attachment links to proxy script to access the documents here.
    $highestNum = 0;
    if(isset($attachments)) {
      $inputs = '';
      foreach($attachments as $attachment) {
        echo '<a href="' . $rootdir . 'download?item=asset&value=' . $attachment['asset'] . '&num=' . $attachment['num'] . '&extension=' . $attachment['extension'] . '&name=' . $attachment['name'] . '">' . $attachment['name'] . '.' . $attachment['extension'] . '</a><br />';
        $inputs .= '<input type="checkbox" name="' . $othFieldNames['removeAtt'] . '[]" id="' . $othFieldNames['removeAtt'] . $attachment['num'] . '" value="' . $attachment['asset'] . '_' . $attachment['num'] . '.' . $attachment['extension'] . '" title="' . $attachment['name'] . '.' . $attachment['extension'] . '"';
        if(is_array($_POST[$othFieldNames['removeAtt']]) && in_array($attachment['asset'] . '_' . $attachment['num'] . '.' . $attachment['extension'], $_POST[$othFieldNames['removeAtt']])) { 
          $inputs .= ' checked="checked"';
        }
        $inputs .= ' /><label for="' . $othFieldNames['removeAtt'] . $attachment['num'] . '">Delete</label><br />';
       $highestNum = ($attachment['num'] > $highestNum ? $attachment['num'] : $highestNum);
      }

      echo '</div><div class="floatLeft padL10">' . $inputs . '</div>';
    }
    ?>
    <input class="clear-both block" type="file" name="<?php echo $othFieldNames['attachment']; ?>" id="<?php echo $othFieldNames['attachment']; ?>" class="width250" value="<?php if(isset($_POST[$othFieldNames['attachment']])) { echo htmlspecialchars($_POST[$othFieldNames['attachment']]); } ?>"<?php if($u['role'] <= ISLE\Models\Role::USER) { echo ' disabled="disabled"'; } ?> />
    <span id="msg-<?php echo $othFieldNames['attachment']; ?>" class="err"><?php if(isset($errors['attachment'])){ echo htmlspecialchars($errors['attachment']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <input type="hidden" name="<?php echo $othFieldNames['attachmentNum']; ?>" id="<?php echo $othFieldNames['attachmentNum']; ?>" value="<?php echo $highestNum; ?>" />
  
  <div class="formItem newline" id="actionsSection">
    <?php if($action == 'edit') { ?>
      <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
      <button type="submit" name="updateBtn" value="update" id="saveBtn" class="btn btn-primary">Save</button>
      <button type="submit" name="cancelBtn" value="cancel" id="cancelBtn" class="btn">Cancel</button>
      <button type="submit" name="deleteBtn" value="delete" id="deleteBtn" class="btn btn-danger"><i class="icon-trash icon-white"></i> Delete</button>
      <?php } else { ?>
      <button type="submit" name="cancelBtn" value="cancel" id="cancelBtn" class="btn">Cancel</button>
      <?php } ?>
    <?php } 
    else { ?>
      <button type="submit" name="addBtn" value="add" id="addBtn" class="btn btn-primary">Add</button>
      <button type="submit" name="cancelBtn" value="cancel" id="cancelBtn" class="btn">Cancel</button>
    <?php } ?>
    <span id="actionsSubSection"></span>
  </div>
</form>

<?php if($action == 'edit') { ?>

<ul id="VerColMenu" class="marginT10">
  <li><h4 role="button" tabindex="0"><span class="expander">+</span>Transaction History </h4>
    <div id="transData">
    <div class="listControls" id="listNav">
      <!-- floats must come first for ie7 -->
      <b id="firstItem"></b>-<b id="lastItem"></b> of <b id="totalItems"></b>
      <div class="btn-group">
      <a id="prevPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Previous Page"><i class="icon-chevron-left"></i></a>
      <a id="nextPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Next Page"><i class="icon-chevron-right"></i></a>
      </div>
    </div>

    <table id="nodeTable" class="table table-striped"></table>
    </div>
  </li>
    
</ul>
<?php } 

  if($u['role'] > ISLE\Models\Role::VIEWER) {
    require_once 'assetCheckoutDialog.php';
  }
  require_once 'assetCheckinDialog.php';

  if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) {
    require_once 'assetRestrictDialog.php';
    require_once 'assetUnrestrictDialog.php';
  }
?>