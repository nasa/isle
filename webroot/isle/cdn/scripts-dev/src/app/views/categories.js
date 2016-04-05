define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();

    var rowsClickable = false;
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
      rowsClickable = true;
    }

    var options = {
      fieldNames: {"id":"hidId","name":"txtName","parent":"txtParent"},
      limit: 500,
      tree: true,
      order: [{col: 'name'}],
      itemName: 'category',
      itemNamePlural: 'categories',
      buildTable: function(retVal) {
        var editTableCol = '';
        
        $('#nodeTable').html('');
        
        //loop over categories and if parent is null add them to the table.
        //then get the children of each top level node and add them, and continue until you have built the tree.
        var descendants = [];
        var parents = retVal['parents'];
        var cur = '';
        var indent = '';
        var i = 0;
        recurse("", -1);
        
        function recurse(id, level) {
          if(id != "") {
            indent = '';
            i = 1;
            while(i < level+1) {
              if(i % level == 0) {
                indent += '-&nbsp;&nbsp;';
              }
              else {
                indent += '&nbsp;&nbsp;&nbsp;';
              }
              i++;
            }
            cur = retVal['items'][id];
            
            editTableCol = '';
            if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
              editTableCol = '<td><span role="button" tabindex="0" id="' + cur['id'] + '" title="Edit"><i class="icon-edit"></i></span></td>';
            }
            
            $('#nodeTable').append('<tr>' + editTableCol + '<td>' + indent + Util.htmlEncode(Util.abbreviate(cur['name'])) + '</td></tr>');
          }
          if($.isArray(parents[id])) {
            $.each(parents[id], function(index, value){
              recurse(value, level+1);
            });
          }
          return;
        }
      },
      modalInit: function(retVal) {
        var selectHTML = '<option value="">-- Select a parent</option>'
        
        $.each(retVal['items'], function(index, val){
            selectHTML += '<option value="' + val['id'] + '">' + Util.htmlEncode(Util.abbreviate(val['name'])) + '</option>';
        });
        
        $('#modalDialog select[name="' + this.fieldNames['parent'] + '"]').html(selectHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var thisCategory = retVal['items'][idVal];
        var nameVal = thisCategory['name'];
        var parentVal = thisCategory['parent'];
        var parentCategory = retVal['items'][parentVal];
        var parentValText = '';
        if(typeof parentCategory != 'undefined') {
          parentValText = parentCategory['name'];
        }

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['name'] + '"]').val(nameVal);
        
        var selectHTML = '<option value="">-- Select a parent</option>'
        var descendants = [];
        
        Util.getDescendants(idVal, descendants, retVal['parents']);
        
        $.each(retVal['items'], function(index, val){
          //if the category is not equal to itself or one of its descendants 
          if(val['id'] != idVal && $.inArray(val['id'], descendants) < 0) {
            selectHTML += '<option value="' + val['id'] + '">' + Util.htmlEncode(Util.abbreviate(val['name'])) + '</option>';
          }
        });
        
        $('#modalDialog select[name="' + this.fieldNames['parent'] + '"]').html(selectHTML).val(parentVal);
        $('#modalDialog .ui-combobox input').val(parentValText);
        
      },
      duplicate: {"field": "name", "errorMsg": "That name already exists"},
      rowsClickable: rowsClickable
    }

    NodeMgr.intialize(options);
    
    $( "#" + options.fieldNames['parent'] ).combobox();
  });
});