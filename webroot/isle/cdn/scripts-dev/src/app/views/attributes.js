define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();

    var rowsClickable = false;
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
      rowsClickable = true;
    }

    var options = {
      fieldNames: {"id":"hidId","name":"txtName","type":"selType"},
      order: [{col: 'name'}],
      itemName: 'attribute',
      buildTable: function(retVal) {
        var editHeadCol = '';
        var editTableCol = '';
        
        if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
          editHeadCol = '<th></th>';
        }
        
        var tableHTML = '<thead>' + editHeadCol + '<th>Attribute</th><th>Type/Unit</th></thead><tbody>';
        var abbr = '';

        $.each(retVal['items'], function(index, val){
          
          editTableCol = '';
          if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
            editTableCol = '<td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td>';
          }
          
          tableHTML += '<tr>' + editTableCol + '<td>' + Util.htmlEncode(Util.abbreviate(val['name'], 50)) + '</td>';
          abbr = '';
          if(val['AttributeType_abbr'] !== null) {
            abbr = ' (' + Util.htmlEncode(Util.abbreviate(val['AttributeType_abbr'], 50)) + ')';
          }
          tableHTML += '<td>' + Util.htmlEncode(Util.abbreviate(val['AttributeType_unit'], 45)) + abbr + '</td></tr>';
        });
        tableHTML += '</tbody>';
        $('#nodeTable').html(tableHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var rowData = Util.getObjects(retVal['items'], 'id', idVal)[0];
        var nameVal = rowData['name'];
        var typeVal = rowData['type'];

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['name'] + '"]').val(nameVal);
        $('#modalDialog select[name="' + this.fieldNames['type'] + '"]').val(typeVal);       
      },
      duplicate: {"field": "name", "errorMsg": "That attribute already exists"},
      rowsClickable: rowsClickable
    }

    NodeMgr.intialize(options);
  });
});