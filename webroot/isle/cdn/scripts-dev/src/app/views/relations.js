define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();
    
    var rowsClickable = false;
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
      rowsClickable = true;
    }

    var options = {
      fieldNames: {"id":"hidId","name":"txtName"},
      itemName: 'relation',
      order: [{col: 'name'}],
      buildTable: function(retVal) {
        var editTableCol = '';
        
        var tableHTML = '<tbody>';

        $.each(retVal['items'], function(index, val){
          editTableCol = '';
          if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
            editTableCol = '<td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td>';
          }
          
          tableHTML += '<tr>' + editTableCol + '<td>' + Util.htmlEncode(Util.abbreviate(val['name'], 50)) + '</td></tr>';
        });
        tableHTML += '</tbody>'
        $('#nodeTable').html(tableHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var nameVal = Util.getObjects(retVal['items'], 'id', idVal)[0]['name'];

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['name'] + '"]').val(nameVal);
      },
      duplicate: {"field": "name", "errorMsg": "That relation already exists"},
      rowsClickable: rowsClickable
    }

    NodeMgr.initialize(options);
  });
});