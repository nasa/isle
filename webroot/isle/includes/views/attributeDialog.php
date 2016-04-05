<?php if(!isset($idsuffix)) {
  $idsuffix = '';
} ?>

<div class="modal hide" id="modalDialog<?php echo $idsuffix; ?>" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm<?php echo $idsuffix; ?>" id="modalForm<?php echo $idsuffix; ?>" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['attributeForm']['id']; ?>" id="<?php echo $fieldNames['attributeForm']['id']; ?>" value="" />
  <div class="modal-body">
    <div class="formItem first">
      <label for="<?php echo $fieldNames['attributeForm']['name']; ?>">Name</label>
      <input type="text" name="<?php echo $fieldNames['attributeForm']['name']; ?>" id="<?php echo $fieldNames['attributeForm']['name']; ?>" class="width250" />
      <span id="msg-<?php echo $fieldNames['attributeForm']['name']; ?>" class="err">&nbsp;</span>
    </div>
    
    
    <div class="formItem newline">
      <label for="<?php echo $fieldNames['attributeForm']['type']; ?>">Type</label>
      <select name="<?php echo $fieldNames['attributeForm']['type']; ?>" id="<?php echo $fieldNames['attributeForm']['type']; ?>">
        <option value="">-- Select a type</option>
        <!-- populate with data from database -->
        <?php
        
          $listHTML = '';
          
          function recurse($id, $level, $items, $parents, &$listHTML) {
            if($id != "") {
              $indent = '';
              $i = 0;
              while($i < $level) {
                $indent .= '&nbsp;&nbsp;';
                $i++;
              }
              $cur = $items[$id];
              $abbr = '';
              if($cur['abbr'] != null) {
                $abbr = ' (' . htmlspecialchars($cur['abbr']) . ')';
              }
              
              if($id < 5) {
                $listHTML .= '<option value="' . $cur['id'] . '" class="baseGroup">' . $indent . htmlspecialchars($cur['unit']) . $abbr . '</option>';
              }
              else {
                $listHTML .= '<option value="' . $cur['id'] . '">' . $indent . htmlspecialchars($cur['unit']) . $abbr . '</option>';
              }
            }
            if(array_key_exists($id, $parents) && is_array($parents[$id])) {
              foreach($parents[$id] as $value) {
                recurse($value, $level + 1, $items, $parents, $listHTML);
              }
            }
            return;
          }
          
          if(count($items) > 0) {
            recurse("", -1, $items, $parents, $listHTML);
          }

          echo $listHTML;
        ?>
      </select>
      <span id="msg-<?php echo $fieldNames['attributeForm']['type']; ?>" class="err">&nbsp;</span>
    </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>