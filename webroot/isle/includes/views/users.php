<div class="listRightCont">
  <div class="listRight">
    <div class="listControls" id="listNav">
      <!-- floats must come first for ie7 -->
      <b id="firstItem"></b>-<b id="lastItem"></b> of <b id="totalItems"></b>
      <div class="btn-group">
        <a id="prevPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Previous Page"><i class="icon-chevron-left"></i></a>
        <a id="nextPageBtn" class="btn btn-small" tabindex="0" role="button" aria-label="Next Page"><i class="icon-chevron-right"></i></a>
      </div>
    </div>
    <div class="scroll-pane">
    <table id="nodeTable" class="table table-striped table-clickable">
    </table>
    </div>
  </div>
</div>
<div class="listLeft center">
  <button type="button" name="addItemBtn" value="add" id="addItemBtn" class="btn btn-primary" autofocus="autofocus"><i class="icon-plus icon-white"></i> Add User</button>
</div>

<div class="modal hide" id="modalDialog" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-header">
    <button type="button" class="close" aria-label="Close">&times;</button>
    <h3 id="modalTitle"></h3>
  </div>
  <form name="modalForm" id="modalForm" action="" method="post">
    <input type="hidden" name="<?php echo $fieldNames['id']; ?>" id="<?php echo $fieldNames['id']; ?>" value="" />
  <div class="modal-body">
    
    <div class="formItem first">
      <label for="<?php echo $fieldNames['uid']; ?>">User</label>
      <select name="<?php echo $fieldNames['uid']; ?>" id="<?php echo $fieldNames['uid']; ?>">
        <option value="">-- Select a user</option>
          <!-- populate with data from database -->
          <?php
            foreach($employees as $employee) {

              echo '<option data-email="' . $employee['PRIMARYEMAIL'] . '" value="' . $employee['EMPLOYEENUMBER'] . '" >' . htmlspecialchars($employee['FULL_NAME']) . '</option>';
            }
          ?>
      </select>&nbsp;&nbsp;&nbsp;&nbsp;
      <span id="msg-<?php echo $fieldNames['uid']; ?>" class="err">&nbsp;</span>
    </div>
    
    <input type="hidden" name="<?php echo $fieldNames['name']; ?>" id="<?php echo $fieldNames['name']; ?>" />
    
    <input type="hidden" name="<?php echo $fieldNames['email']; ?>" id="<?php echo $fieldNames['email']; ?>" />
    
    <div class="formItem newline">
      <label for="<?php echo $fieldNames['role']; ?>">Role</label>
      <select name="<?php echo $fieldNames['role']; ?>" id="<?php echo $fieldNames['role']; ?>">
        <option value="">-- Select a role</option>
        <!-- populate with data from database -->
        <?php
          foreach($roles as $role) {
            $selected = '';
            if(isset($_POST[$fieldNames['role']]) && $_POST[$fieldNames['role']] == $role['id']) {
              $selected = ' selected="selected"';
            }
            echo '<option value="' . $role['id'] . '"' . $selected . '>' . htmlspecialchars($role['name']) . '</option>';
          }
        ?>
      </select>
      <span id="msg-<?php echo $fieldNames['role']; ?>" class="err">&nbsp;</span>
    </div>
    <div class="formItem newline">
      <label for="chkEmail" id="labEmail"><input type="checkbox" name="chkEmail" value="" id="chkEmail" />Send a welcome email <span id="toEmail">&nbsp;</span><span id="msg-chkEmail" class="err">&nbsp;</span></label>
    </div>
  </div>
  <div class="modal-footer">
  </div>
  </form>
</div>