define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var NodeMgr = new NodeManager();

    var rowsClickable = false;
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
      rowsClickable = true;
    }

    var options = {
      fieldNames: {"id":"hidId","center":"selCenter","bldg":"txtBldg","room":"txtRoom"},
      itemName: 'location',
      order: [{col: 'center'},{col: 'bldg'},{col: 'room'}],
      buildHead: function() {
        return '<thead><th>Center</th><th>Building</th><th>Room</th></thead>';
      },
      buildTable: function(retVal) {
        var editHeadCol = '';
        var editTableCol = '';
        
        if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
          editHeadCol = '<th></th>';
        }
        
        var tableHTML = '<thead>' + editHeadCol + '<th>Center</th><th>Building</th><th>Room</th></thead><tbody>';

        $.each(retVal['items'], function(index, val){
          
          editTableCol = '';
          if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
            editTableCol = '<td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td>';
          }
          
          tableHTML += '<tr>' + editTableCol + '<td><a href="' + Util.rootdir + 'assets?item=location&value=' + Util.htmlEncode(val['center']) + '">' + Util.htmlEncode(val['center']) + '</a></td>';
          tableHTML += '<td>';
          if(Util.htmlEncode(val['bldg']) != '') {
            tableHTML += '<a href="' + Util.rootdir + 'assets?item=location&value=' + Util.htmlEncode(val['center']) + ',' + Util.htmlEncode(val['bldg']) + '">' + Util.htmlEncode(val['bldg']) + '</a>';
          }
          tableHTML += '</td>';
          tableHTML += '<td>';
          if(Util.htmlEncode(val['room']) != '') {
            tableHTML += '<a href="' + Util.rootdir + 'assets?item=location&value=' + Util.htmlEncode(val['center']) + ',' + Util.htmlEncode(val['bldg']) + ',' + Util.htmlEncode(val['room']) + '">' + Util.htmlEncode(val['room']) + '</a>';
          }
          tableHTML += '</td></tr>';
        });
        tableHTML += '</tbody>'
        $('#nodeTable').html(tableHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var rowData = Util.getObjects(retVal['items'], 'id', idVal)[0];
        var centerVal = rowData['center'];
        var bldgVal = rowData['bldg'];
        var roomVal = rowData['room'];

        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['center'] + '"]').val(centerVal);
        $('#modalDialog input[name="' + this.fieldNames['bldg'] + '"]').val(bldgVal);
        $('#modalDialog input[name="' + this.fieldNames['room'] + '"]').val(roomVal);
      },
      duplicate: {"field": "room", "errorMsg": "This location already exists"},
      rowsClickable: rowsClickable
    }

    NodeMgr.initialize(options);
    
    var opts = {
      fieldNames: options.fieldNames,
      itemName: options.itemName,
      select: ['center'],
      filter: {
        cols: []
      }
    }
    
    $('#' + options.fieldNames['center']).autocomplete(NodeMgr.buildACOptions(opts));
  
    var opts2 = {
      fieldNames: options.fieldNames,
      itemName: options.itemName,
      select: ['bldg'],
      filter: {
        cols: ['center']
      }
    }
    
    $('#' + options.fieldNames['bldg']).autocomplete(NodeMgr.buildACOptions(opts2));
    
    var opts3 = {
      fieldNames: options.fieldNames,
      itemName: options.itemName,
      select: ['room'],
      filter: {
        cols: ['center','bldg']
      }
    }
    
    $('#' + options.fieldNames['room']).autocomplete(NodeMgr.buildACOptions(opts3));
  });
});

