define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();

    var options = {
      fieldNames: {"id":"id", "uid":"selUID", "name": "hidName", "email": "hidEmail", "role": "selRole"},
      itemName: 'user',
      order: [{col: 'Role_name'}, {col: 'name'}],
      buildTable: function(retVal) {
        
        var tableHTML = '<thead><th></th><th>User</th><th>Role</th></thead><tbody>';
        var nameText = '';

        $.each(retVal['items'], function(index, val){
          nameText = '<a href="' + Util.rootdir + 'assets?item=user&value=' + Util.htmlEncode(val['uid']) + '">' + Util.htmlEncode(val['name']) + '</a>';
          if(val['email'] != null) {
            nameText += ' <a href="mailto:' + Util.htmlEncode(val['email']) + '" aria-label="send email to user"><i class="icon-envelope"></i></a>';
          }
          tableHTML += '<tr><td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td><td>' + nameText + '</td>';
          tableHTML += '<td>' + val['Role_name'] + '</td></tr>';
          
        });
        tableHTML += '</tbody>'
        $('#nodeTable').html(tableHTML);
      },
      modalInit: function() {
        $('#toEmail').html('');
        $('#labEmail').show();
        $('#readOnlyUser').remove();
        $('#modalDialog .ui-combobox').show();
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var rowData = Util.getObjects(retVal['items'], 'id', idVal)[0];
        var uidVal = rowData['uid'];
        var nameVal = rowData['name'];
        var emailVal = rowData['email'];
        var roleVal = rowData['role'];
        
        //after the select combo box add the employee name text and a hidden form field and set value to the empno. then remove the combobox from the dom.
        $('#labEmail').hide();
        $('#readOnlyUser').remove();
        $('#modalDialog select[name="' + this.fieldNames['uid'] + '"]').after('<span id="readOnlyUser">' + Util.htmlEncode(nameVal) + '</span>');
        $('#modalDialog .ui-combobox').hide();

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog select[name="' + this.fieldNames['uid'] + '"]').val(uidVal);
        $('#modalDialog input[name="' + this.fieldNames['name'] + '"]').val(nameVal);
        $('#modalDialog input[name="' + this.fieldNames['email'] + '"]').val(emailVal);
        $('#modalDialog select[name="' + this.fieldNames['role'] + '"]').val(roleVal);
        $('#modalDialog .ui-combobox input').val(nameVal);
        
        //if user is the same as logged in user they can't edit or delete.
        if(uidVal == $('html').attr('data-user')) {
          alert("You can't modify your own account. Have another administrator do it for you.");
          return false; //don't show the dialog.
        }
      },
      duplicate: {"field": "uid", "errorMsg": "That user already exists"},
      firstFocus: 'select'
    }

    NodeMgr.intialize(options);
    
    $('#' + options.fieldNames['uid']).combobox({
      selected: function(event, ui) {
        $('#' + options.fieldNames['name']).val($(ui.item).html());
        $('#' + options.fieldNames['email']).val($(ui.item).attr('data-email'));
        $('#chkEmail').val($(ui.item).attr('data-email'));
        $('#toEmail').html('to ' + Util.htmlEncode($(ui.item).attr('data-email')));
      }
    });
  });
});