<h2><?php if($action == 'edit') { echo 'Asset Model #' . $itemToEdit; } else { echo 'Add Asset Model'; } ?></h2>

<?php if(isset($_SESSION['fromItem'])) { ?>
  
<span class="hidden" id="fromItem" data-value="<?php echo $_SESSION['fromItem']; ?>"></span>
  
<?php  } ?>

<form name="modelForm" id="modelForm" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrfToken" value="<?php echo $csrfToken ?>" />
  <input type="hidden" name="<?php echo $fieldNames['assetModelForm']['id']; ?>" id="<?php echo $fieldNames['assetModelForm']['id']; ?>" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['id']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['id']]); } ?>" />
  
  <div class="formItem first<?php if(isset($errors['desc'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetModelForm']['desc']; ?>">Description</label>
    <input type="text" name="<?php echo $fieldNames['assetModelForm']['desc']; ?>" id="<?php echo $fieldNames['assetModelForm']['desc']; ?>" class="width400" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['desc']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['desc']]); } ?>" autofocus="autofocus" />
    <span id="msg-<?php echo $fieldNames['assetModelForm']['desc']; ?>" class="err"><?php if(isset($errors['desc'])){ echo htmlspecialchars($errors['desc']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['mfr'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetModelForm']['mfr']; ?>">Manufacturer</label>
    <select name="<?php echo $fieldNames['assetModelForm']['mfr']; ?>" id="<?php echo $fieldNames['assetModelForm']['mfr']; ?>">
      <option value="">-- Select a manufacturer</option>
      <!-- populate with data from database -->
      <?php
        foreach($manufacturers as $manufacturer) {
          $selected = '';
          if(isset($_POST[$fieldNames['assetModelForm']['mfr']]) && $_POST[$fieldNames['assetModelForm']['mfr']] == $manufacturer['id']) {
            $selected = ' selected="selected"';
          }
          echo '<option value="' . $manufacturer['id'] . '"' . $selected . '>' . htmlspecialchars($manufacturer['name']) . '</option>';
        }
      ?>
    </select>
    <span id="msg-<?php echo $fieldNames['assetModelForm']['model']; ?>" class="err"><?php if(isset($errors['mfr'])){ echo htmlspecialchars($errors['mfr']); } else { echo '&nbsp;'; } ?></span>
    <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
    <br /><button type="button" name="addMfrBtn" value="add" id="addMfrBtn" class="btn btn-mini"><i class="icon-plus"></i> Add Manufacturer</button>
    <?php } ?>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['model'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetModelForm']['model']; ?>">Model</label>
    <input type="text" name="<?php echo $fieldNames['assetModelForm']['model']; ?>" id="<?php echo $fieldNames['assetModelForm']['model']; ?>" class="width250" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['model']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['model']]); } ?>" />
    <span id="msg-<?php echo $fieldNames['assetModelForm']['model']; ?>" class="err"><?php if(isset($errors['model'])){ echo htmlspecialchars($errors['model']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['series'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetModelForm']['series']; ?>">Series</label>
    <input type="text" name="<?php echo $fieldNames['assetModelForm']['series']; ?>" id="<?php echo $fieldNames['assetModelForm']['series']; ?>" class="width250" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['series']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['series']]); } ?>" />
    <span id="msg-<?php echo $fieldNames['assetModelForm']['series']; ?>" class="err"><?php if(isset($errors['series'])){ echo htmlspecialchars($errors['series']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <input type="hidden" name="<?php echo $fieldNames['assetModelForm']['img']; ?>" id="<?php echo $fieldNames['assetModelForm']['img']; ?>" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['img']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['img']]); } ?>" />
  
  <div class="formItem newline<?php if(isset($errors['image'])){ echo ' inError'; } ?>">
    <label for="<?php echo $othFieldNames['image']; ?>">Image <span class="ro">(jpg, gif, png)</span></label>
    <?php 
    
    if(isset($modelImg)) {
      
      $imgAltTxt = 'model image';
      
      if(isset($_POST[$fieldNames['assetModelForm']['desc']])) {
        $imgAltTxt = htmlspecialchars($_POST[$fieldNames['assetModelForm']['desc']]);
      }
      
      echo '<a href="' . $modelImg['target'] . '">';
      echo '<img src="' . $modelImg['source'] . '" alt="' . $imgAltTxt . '"/></a><br /><input type="checkbox" name="' . $othFieldNames['removeImage'] . '" id="' . $othFieldNames['removeImage'] . '"';
      if(isset($_POST[$othFieldNames['removeImage']])) { 
        echo ' checked="checked"';
      }
      echo ' /><label for="' . $othFieldNames['removeImage'] . '">Remove image</label><br />';
    }
      
    ?>
    <input type="file" name="<?php echo $othFieldNames['image']; ?>" id="<?php echo $othFieldNames['image']; ?>" class="width250" value="<?php if(isset($_POST[$othFieldNames['image']])) { echo htmlspecialchars($_POST[$othFieldNames['image']]); } ?>" />
    <span id="msg-<?php echo $othFieldNames['image']; ?>" class="err"><?php if(isset($errors['image'])){ echo htmlspecialchars($errors['image']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['url'])){ echo ' inError'; } ?>">
    <label for="<?php echo $fieldNames['assetModelForm']['url']; ?>">URL</label>
    <input type="text" name="<?php echo $fieldNames['assetModelForm']['url']; ?>" id="<?php echo $fieldNames['assetModelForm']['url']; ?>" class="width250" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['url']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['url']]); } ?>" />
    <span id="msg-<?php echo $fieldNames['assetModelForm']['url']; ?>" class="err"><?php if(isset($errors['url'])){ echo $errors['url']; } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <div class="formItem newline<?php if(isset($errors['categories'])){ echo ' inError'; } ?>">
    <label for="<?php echo $othFieldNames['categories']; ?>">Categories</label>
    <input type="hidden" name="<?php echo $othFieldNames['categoryLabels']; ?>" id="<?php echo $othFieldNames['categoryLabels']; ?>" value="<?php if(isset($_POST[$othFieldNames['categoryLabels']])) { echo htmlspecialchars($_POST[$othFieldNames['categoryLabels']]); } ?>" />
    <input type="text" name="<?php echo $othFieldNames['categories']; ?>" id="<?php echo $othFieldNames['categories']; ?>" class="width250" value="<?php if(isset($_POST[$othFieldNames['categories']])) { echo htmlspecialchars($_POST[$othFieldNames['categories']]); } ?>" data-postexists="<?php if(isset($_POST[$othFieldNames['categories']])) { echo 'true'; } ?>" />
    <span id="msg-<?php echo $othFieldNames['categories']; ?>" class="err"><?php if(isset($errors['categories'])){ echo htmlspecialchars($errors['categories']); } else { echo '&nbsp;'; } ?></span>
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
        echo '<a href="' . $rootdir . 'download?item=model&value=' . $attachment['model'] . '&num=' . $attachment['num'] . '&extension=' . $attachment['extension'] . '&name=' . $attachment['name'] . '">' . $attachment['name'] . '.' . $attachment['extension'] . '</a><br />';
        $inputs .= '<input type="checkbox" name="' . $othFieldNames['removeAtt'] . '[]" id="' . $othFieldNames['removeAtt'] . $attachment['num'] . '" value="' . $attachment['model'] . '_' . $attachment['num'] . '.' . $attachment['extension'] . '" title="' . $attachment['name'] . '.' . $attachment['extension'] . '"';
        if(is_array($_POST[$othFieldNames['removeAtt']]) && in_array($attachment['model'] . '_' . $attachment['num'] . '.' . $attachment['extension'], $_POST[$othFieldNames['removeAtt']])) {
          $inputs .= ' checked="checked"';
        }
        $inputs .= ' /><label for="' . $othFieldNames['removeAtt'] . $attachment['num'] . '">Delete</label><br />';
       $highestNum = ($attachment['num'] > $highestNum ? $attachment['num'] : $highestNum);
      }

      echo '</div><div class="floatLeft padL10">' . $inputs . '</div>';
    }
    ?>
    <input class="clear-both block" type="file" name="<?php echo $othFieldNames['attachment']; ?>" id="<?php echo $othFieldNames['attachment']; ?>" class="width250" value="<?php if(isset($_POST[$othFieldNames['attachment']])) { echo htmlspecialchars($_POST[$othFieldNames['attachment']]); } ?>" />
    <span id="msg-<?php echo $othFieldNames['attachment']; ?>" class="err"><?php if(isset($errors['attachment'])){ echo htmlspecialchars($errors['attachment']); } else { echo '&nbsp;'; } ?></span>
  </div>
  
  <input type="hidden" name="<?php echo $othFieldNames['attachmentNum']; ?>" id="<?php echo $othFieldNames['attachmentNum']; ?>" value="<?php echo $highestNum; ?>" />
  
  <div class="formItem newline">
    <?php if($action == 'edit') { ?>
      <button type="submit" name="updateBtn" value="update" id="saveBtn" class="btn btn-primary">Save</button>
      <button type="submit" name="cancelBtn" value="cancel" id="cancelBtn" class="btn">Cancel</button>
      <button type="submit" name="deleteBtn" value="delete" id="deleteBtn" class="btn btn-danger"><i class="icon-trash icon-white"></i> Delete</button>
    <?php } 
    else { ?>
      <button type="submit" name="addBtn" value="add" id="addBtn" class="btn btn-primary">Add</button>
      <button type="submit" name="cancelBtn" value="cancel" id="cancelBtn" class="btn">Cancel</button>
    <?php } ?>
  </div>
</form>

<?php 
  if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) {
    $idsuffix = "Mfr";
    require_once 'manufacturerDialog.php';
  }
?>

<?php if($action == 'edit') { ?>

<h4 class="marginT10">Attributes <button type="button" name="addItemBtn" value="add" id="addItemBtn" class="btn btn-mini"><i class="icon-plus"></i> Add</button></h4>

<table id="nodeTable" class="table table-striped table-clickable marginT5"></table>

<div class="modal hide" id="modalDialog" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm" id="modalForm" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['attributesForm']['id']; ?>" id="<?php echo $fieldNames['attributesForm']['id']; ?>" value="" />
    <input type="hidden" name="<?php echo $fieldNames['attributesForm']['model']; ?>" id="<?php echo $fieldNames['attributesForm']['model']; ?>" value="<?php if(isset($_POST[$fieldNames['assetModelForm']['id']])) { echo htmlspecialchars($_POST[$fieldNames['assetModelForm']['id']]); } ?>" />
  <div class="modal-body">
      <div class="formItem first">
        <label for="<?php echo $fieldNames['attributesForm']['attribute']; ?>">Attribute</label>
        <select name="<?php echo $fieldNames['attributesForm']['attribute']; ?>" id="<?php echo $fieldNames['attributesForm']['attribute']; ?>">
          <option value="">-- Select an attribute</option>
          <!-- populate with data from database -->
          <?php
            foreach($attributes as $attribute) {
              $abbr = '';
              if($attribute['AttributeType_abbr'] != null) {
                $abbr = ' (' . htmlspecialchars($attribute['AttributeType_abbr']) . ')';
              }
              
              echo '<option value="' . $attribute['id'] . '" data-desc="' . htmlspecialchars($attribute['AttributeType_unit']) . $abbr . '">' . htmlspecialchars($attribute['name']) . '</option>';
            }
          ?>
        </select>&nbsp;&nbsp;&nbsp;&nbsp;
        <span id="msg-<?php echo $fieldNames['attributesForm']['attribute']; ?>" class="err">&nbsp;</span>
      </div>
      <div class="formItem newline">
        <label for="<?php echo $fieldNames['attributesForm']['value']; ?>" id="valueLabel">Value</label>
        <input type="text" name="<?php echo $fieldNames['attributesForm']['value']; ?>" id="<?php echo $fieldNames['attributesForm']['value']; ?>" class="width250" />
        <span id="msg-<?php echo $fieldNames['attributesForm']['value']; ?>" class="err">&nbsp;</span>
      </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>

<h4 class="marginT10">Relationships <button type="button" name="addItemBtn2" value="add" id="addItemBtn2" class="btn btn-mini"><i class="icon-plus"></i> Add</button></h4>

<table id="nodeTable2" class="table table-striped table-clickable marginT5"></table>

<div class="modal hide" id="modalDialog2" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm" id="modalForm2" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['relationshipsForm']['id']; ?>" id="<?php echo $fieldNames['relationshipsForm']['id']; ?>" value="" />
  <div class="modal-body">
    <div class="formItem first">
      <label for="<?php echo $fieldNames['relationshipsForm']['source']; ?>">Source</label>
      <select name="<?php echo $fieldNames['relationshipsForm']['source']; ?>" id="<?php echo $fieldNames['relationshipsForm']['source']; ?>">
        <option value="">-- Select a model</option>
      <!-- populate with data from database -->
      <?php
        foreach($models as $model) {
          echo '<option value="' . $model['id'] . '"' . '>' . htmlspecialchars($model['Manufacturer_name']) . ' ' . htmlspecialchars($model['model']) . '</option>';
        }
      ?>
      </select>
      <span id="msg-<?php echo $fieldNames['relationshipsForm']['source']; ?>" class="err">&nbsp;</span>
    </div>
      
    <div class="formItem newline">
      <label for="<?php echo $fieldNames['relationshipsForm']['relation']; ?>">Relation</label>
      <select name="<?php echo $fieldNames['relationshipsForm']['relation']; ?>" id="<?php echo $fieldNames['relationshipsForm']['relation']; ?>">
        <option value="">-- Select a relation</option>
        <!-- populate with data from database -->
        <?php
          foreach($relations as $relation) {
            echo '<option value="' . $relation['id'] . '">' . htmlspecialchars($relation['name']) . '</option>';
          }
        ?>
      </select>
      <span id="msg-<?php echo $fieldNames['relationshipsForm']['relation']; ?>" class="err">&nbsp;</span>
      <?php if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) { ?>
      <br /><button type="button" name="addRelationBtn" value="add" id="addRelationBtn" class="btn btn-mini"><i class="icon-plus"></i> Add Relation</button>
      <?php } ?>
    </div>
    
    <div class="formItem newline">
      <label for="<?php echo $fieldNames['relationshipsForm']['target']; ?>">Target</label>
      <select name="<?php echo $fieldNames['relationshipsForm']['target']; ?>" id="<?php echo $fieldNames['relationshipsForm']['target']; ?>">
        <option value="">-- Select a model</option>
      <!-- populate with data from database -->
      <?php
        foreach($models as $model) {
          echo '<option value="' . $model['id'] . '"' . '>' . htmlspecialchars($model['Manufacturer_name']) . ' ' . htmlspecialchars($model['model']) . '</option>';
        }
      ?>
      </select>
      <span id="msg-<?php echo $fieldNames['relationshipsForm']['target']; ?>" class="err">&nbsp;</span>
    </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>

<?php 
  if($u['role'] >= ISLE\Models\Role::CONTRIBUTOR) {
    $idsuffix = "Relation";
    require_once 'relationDialog.php';
  }
?>

<?php } ?>