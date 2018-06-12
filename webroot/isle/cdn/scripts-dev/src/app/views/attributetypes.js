define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();

    var rowsClickable = false;
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
      rowsClickable = true;
    }

    var options = {
      fieldNames: {"id":"hidId","unit":"txtUnit","abbr":"txtAbbr","parent":"txtParent"},
      limit: 500,
      tree: true,
      order: [{col: 'unit'}],
      itemName: 'Attribute Type',
      modelName: 'AttributeType',
      buildTable: function(retVal) {
        var editTableCol = '';
        var editTableCol2 = '';
       
        $('#nodeTable').html('');
        
        //loop over types and if parent is null add them to the table.
        //then get the children of each top level node and add them, and continue until you have built the tree.
        var descendants = [];
        var parents = retVal['parents'];
        var cur = '';
        var indent = '';
        var i = 0;
        var abbr = '';
        recurse("", -1);
        
        function recurse(id, level) {
          if(id != "") {
            indent = '';
            i = 0;
            while(i < level) {
              indent += '&nbsp;&nbsp;'
              i++;
            }
            cur = retVal['items'][id];
            abbr = '';
            if(cur['abbr'] !== null) {
              abbr = ' (' + Util.htmlEncode(Util.abbreviate(cur['abbr'], 50)) + ')';
            }
            
            editTableCol = '';
            editTableCol2 = '';
            if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
              editTableCol = '<td><span role="button" tabindex="0" id="' + cur['id'] + '" title="Edit"><i class="icon-edit"></i></span></td>';
              editTableCol2 = '<td></td>';
            }
            
            if(cur['id'] < 5) {
              $('#nodeTable').append('<tr class="unclickable">' + editTableCol2 + '<td class="bold">' + indent + Util.htmlEncode(Util.abbreviate(cur['unit'])) + abbr + '</td></tr>');
            }
            else {
              $('#nodeTable').append('<tr>' + editTableCol + '<td>' + indent + Util.htmlEncode(Util.abbreviate(cur['unit'])) + abbr + '</td></tr>');
            }
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
            selectHTML += '<option value="' + val['id'] + '">' + Util.htmlEncode(Util.abbreviate(val['unit'])) + '</option>';
        });
        
        $('#modalDialog select[name="' + this.fieldNames['parent'] + '"]').html(selectHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var thisAttributeType = retVal['items'][idVal];
        var unitVal = thisAttributeType['unit'];
        var abbrVal = thisAttributeType['abbr'];
        var parentVal = thisAttributeType['parent'];
        var parentAttributeType = retVal['items'][parentVal];
        var parentValText = '';
        var abbr = '';
        if(typeof parentAttributeType != 'undefined') {
          if(parentAttributeType['abbr'] !== null) {
            abbr = ' (' + Util.htmlEncode(Util.abbreviate(parentAttributeType['abbr'], 50)) + ')';
          }
          parentValText = parentAttributeType['unit'] + abbr;
        }

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['unit'] + '"]').val(unitVal);
        $('#modalDialog input[name="' + this.fieldNames['abbr'] + '"]').val(abbrVal);
        
        var selectHTML = '<option value="">-- Select a parent</option>'
        var descendants = [];
        
        Util.getDescendants(idVal, descendants, retVal['parents']);
        
        $.each(retVal['items'], function(index, val){
          //if the type is not equal to itself or one of its descendants 
          if(val['id'] != idVal && $.inArray(val['id'], descendants) < 0) {
            abbr = '';
            if(val['abbr'] !== null) {
              abbr = ' (' + Util.htmlEncode(Util.abbreviate(val['abbr'], 50)) + ')';
            }
            selectHTML += '<option value="' + val['id'] + '">' + Util.htmlEncode(Util.abbreviate(val['unit'])) + abbr + '</option>';
          }
        });
        
        $('#modalDialog select[name="' + this.fieldNames['parent'] + '"]').html(selectHTML).val(parentVal);
        $('#modalDialog .ui-combobox input').val(parentValText);
        
      },
      duplicate: {"field": "unit", "errorMsg": "That unit already exists"},
      rowsClickable: rowsClickable
    }

    NodeMgr.initialize(options);
    
    $( "#" + options.fieldNames['parent'] ).combobox();
  });
});