define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();
    
    var rowsClickable = false;
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
      rowsClickable = true;
    }

    var options = {
      fieldNames: {"id":"hidId","name":"txtName","url":"txtUrl"},
      itemName: 'manufacturer',
      order: [{col: 'name'}],
      buildTable: function(retVal) {
        var editTableCol = '';
        
        var tableHTML = '<tbody>';

        $.each(retVal['items'], function(index, val){
          editTableCol = '';
          if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
            editTableCol = '<td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td>';
          }
          
          tableHTML += '<tr>' + editTableCol;
          
          tableHTML += '<td><a href="' + Util.rootdir + 'assets?item=manufacturer&value='+ val['id'] + '">' + Util.htmlEncode(Util.abbreviate(val['name'], 100)) + '</a>';
          
          if(val['url'] !== null && val['url'].length > 0) {
            tableHTML += ' <a href="' + Util.htmlEncode(val['url']) + '" aria-label="link to manufacturer website" target="_blank"><i class="icon-globe"></i></a></td>';
          }
          else {
            tableHTML += '</td>';
          }

          tableHTML += '</tr>';
        });
        tableHTML += '</tbody>'
        $('#nodeTable').html(tableHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var rowData = Util.getObjects(retVal['items'], 'id', idVal)[0];
        var urlVal = rowData['url'];
        var nameVal = rowData['name'];

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['name'] + '"]').val(nameVal);
        $('#modalDialog input[name="' + this.fieldNames['url'] + '"]').val(urlVal);
      },
      duplicate: {"field": "name", "errorMsg": "That manufacturer already exists"},
      rowsClickable: rowsClickable
    }

    NodeMgr.intialize(options);
  });
});