define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    //if the remove image checkbox is checked disable the file field.
    toggleFileUpload($('#chkRemoveImg').is(':checked'));
    
    $('#chkRemoveImg').click(function(){
      toggleFileUpload($(this).is(':checked'));
    });
    
    function toggleFileUpload(disable) {
      if(disable) {
        $('#filImage').prop('disabled', true);
      }
      else {
        $('#filImage').prop('disabled', false);
      }
    }
    
    var NodeMgr = new NodeManager();
    
    var options = {
      itemName: 'model',
      modelName: 'AssetModel',
      itemVal: $('#hidId').val(),
      postURL: 'assetmodels/' + $('#hidId').val(),
      successFunction: function(data, textStatus, jqXHR) {
        if($('#fromItem').length > 0) {
          window.location = '../assets/' + $('#fromItem').attr('data-value');
        }
        else {
          window.location = '../assets/new';
        }
      }
    };
    
    NodeMgr.confirmDelete(options);
    
    var opts = {
      fieldNames: {"id":"hidId","desc":"txtDesc","model":"txtModel","series":"txtSeries","mfr":"selMfr","url":"txtUrl","categories":"txtCategories"},
      itemName: options.modelName,
      select: ['series'],
      filter: {
        cols: ['mfr']
      }
    };

    $('#' + opts.fieldNames['series']).autocomplete(NodeMgr.buildACOptions(opts));
    
    var opts2 = {
      fieldNames: opts.fieldNames,
      itemName: 'Category',
      select: ['id','name'],
      filter: {
        cols: []
      }
    };
    
    var acOptions = NodeMgr.buildACOptions(opts2);
    
    //save the value of the category and category label fields which are comma delimited list of category ids and labels.
    var categoryIds = $('#' + opts.fieldNames['categories']).val();
    var categoryLabels = $('#hidCategoryLabels').val();
    
    //wipe the value of the category field and the category label field.
    $('#' + opts.fieldNames['categories']).val('');
    $('#hidCategoryLabels').val('');
    
    $('#' + opts.fieldNames['categories']).tagit({
      tagSource: acOptions.source,
      requireAutocomplete: true,
      onTagAdded: function(event, tag) {
        //add the tag label to the hidden field
        var curVal = $('#hidCategoryLabels').val();
        var tagLabel = $(tag).find('.tagit-label').html();
        $('#hidCategoryLabels').val(curVal + tagLabel + ',');
      },
      onTagRemoved: function(event, tag) {
        //remove the tag label from the hidden field
        var curVal = $('#hidCategoryLabels').val();
        var tagLabel = $(tag).find('.tagit-label').html();
        curVal = curVal.replace(tagLabel + ',', '');
        $('#hidCategoryLabels').val(curVal);
      }
    });
    
    //add the saved categories back into the tagit box, using the values in the hidden input field for the labels.
    var categoryLabArr = categoryLabels.split(',');
    $(categoryIds.split(",")).each(function (index, value) {
      $('#' + opts.fieldNames['categories']).tagit("createTag", value, categoryLabArr[index]);
    });
    
    $('.tagit .ui-autocomplete-input').bind( "autocompletefocus", function(event, ui) {
      $(this).val( ui.item.label );
      return false;
    });
    
    // don't load categories from db if returning to the form from back or save event as post values will be used instead.
    if($('#' + opts.fieldNames['categories']).attr('data-postexists') != 'true') {

      //get categories from db and put in input field.
      $('.tagit .ui-autocomplete-input').val('Loading categories...');
      $('.tagit .ui-autocomplete-input').attr('disabled', 'disabled');

      $.ajax({
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': $('#csrfToken').html()},
        data: {
          model: 'AssetModelCategory',
          method: 'getAll',
          filter: {
            cols: [{col: 'model', val: $('#hidId').val()}]
          }
        },
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          $('.tagit .ui-autocomplete-input').val('Load failed.');
          $('#msg-' + opts.fieldNames['categories'])
                          .html("I'm sorry. Something went wrong (" + errorThrown + ").");
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          $.each(data['result']['value']['items'], function(index, value) {
            $('#' + opts.fieldNames['categories']).tagit("createTag", value['category'], value['Category_name']);
          });
          $('.tagit .ui-autocomplete-input').val('');
          $('.tagit .ui-autocomplete-input').removeAttr('disabled');
        }
      });
    }
    
    // Custom Attributes
    
    options = {
      modals: true,
      cache: true,
      fieldNames: {"id":"hidId","model":"hidModel","attribute":"selAttribute","value":"txtValue"},
      filter: {
        cols: [{ 
          col: 'model',
          val: $('#hidId').val()
        }]
      },
      order: [{colClass: 'Attribute', col: 'name'}, {col: 'id'}],
      itemName: 'Attribute',
      modelName: 'AssetModelAttribute',
      buildTable: function(retVal) {

        var tableHTML = '<tbody>';
        var unitTxt = '';
        var prevName = '';
        var attrNum = 1;
        var attrNumTxt = '';

        $.each(retVal['items'], function(index, val){
          attrNumTxt = '';
          if(val['Attribute_name'] == prevName) {
            attrNum++;
            attrNumTxt = ' #' + attrNum;
          }
          else if(index < (retVal['items'].length - 1) && val['Attribute_name'] === retVal['items'][index + 1]['Attribute_name']) {
            attrNum = 1;
            attrNumTxt = ' #' + attrNum;
          }
          else {
            attrNum = 1;
          }
          prevName = val['Attribute_name'];
          
          unitTxt = val['AttributeType_abbr'];
          
          if(unitTxt == null) {
            if(val['Attribute_type'] > 4) {
              unitTxt = ' ' + val['AttributeType_unit'];
            }
            else {
              unitTxt = '';
            }
          }
          tableHTML += '<tr><td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td><td>' + Util.htmlEncode(val['Attribute_name'] + attrNumTxt + ': ' + val['value'] + unitTxt) + '</td></tr>';
        });
        
        tableHTML += '</tbody>'
        $('#nodeTable').html(tableHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var rowData = Util.getObjects(retVal['items'], 'id', idVal)[0];
        var attrVal = rowData['attribute'];
        var attrText = rowData['Attribute_name'];
        var valueVal = rowData['value'];
        
        var abbr = '';          
        if(rowData['AttributeType_abbr'] !== null) {
          abbr = ' (' + rowData['AttributeType_abbr'] + ')';
        }
        
        $('#valueLabel').html(Util.htmlEncode('Value : ' + rowData['AttributeType_unit'] + abbr));
        $('#modalDialog input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog input[name="' + this.fieldNames['value'] + '"]').val(valueVal);
        
        $('#modalDialog select[name="' + this.fieldNames['attribute'] + '"]').val(attrVal);
        $('#modalDialog .ui-combobox input').val(attrText);
      },
      duplicate: {"field": "value", "errorMsg": "That attribute and value already exists"},
      modalInit: function() {
        $('#valueLabel').html('Value');
      }
    };

    NodeMgr.intialize(options);
    
    $( "#" + options.fieldNames['attribute'] ).combobox({
      selected: function(event, ui) {
        $('#valueLabel').html(Util.htmlEncode('Value : ' + $(ui.item).attr('data-desc')));
      },
      renderDD: function(e, opts) {
        return $( "<li></li>" )
        .data( "item.autocomplete", opts.item )
        .append( "<a>" + Util.htmlEncode(opts.item.label + ' : ' + $(opts.item.option).attr('data-desc')) + "</a>" )
        .appendTo( opts.ul );
      },
      customRenderOptions: true
    });
    
    // Relationships
    
    var NodeMgr2 = new NodeManager();
    
    var roptions = {
      modals: true,
      cache: true,
      fieldNames: {"id":"hidIdR","source":"selSource","relation":"selRelation","target":"selTarget"},
      filter: {
        cols: [{ 
          col: 'source',
          val: $('#hidId').val()
        },
        { 
          col: 'target',
          val: $('#hidId').val()
        }],
        separator: 'OR'
      },
      itemName: 'Relationship',
      modelName: 'AssetModelRelation',
      buildTable: function(retVal) {

        var tableHTML = '<tbody>';

        $.each(retVal['items'], function(index, val){
          
          tableHTML += '<tr><td><span role="button" tabindex="0" id="' + val['id'] + '" title="Edit"><i class="icon-edit"></i></span></td><td><a href="' + Util.rootdir + 'assets?item=model&value='+ val['source'] + '">' + Util.htmlEncode(val['Manufacturer_name'] + ' ' + val['AssetModel_model']) + '</a> <span class="bold-italic">' + Util.htmlEncode(val['Relation_name']) + '</span> <a href="' + Util.rootdir + 'assets?item=model&value='+ val['target'] + '">' + Util.htmlEncode(val['Manufacturer2_name'] + ' ' + val['AssetModel2_model']) + '</a></td></tr>';
        });
        
        tableHTML += '</tbody>'
        $('#nodeTable2').html(tableHTML);
      },
      fillForm: function(retVal, selectedRow, selectedCell) {
        var idVal = $(selectedCell).attr('id');
        var rowData = Util.getObjects(retVal['items'], 'id', idVal)[0];
        var sourceVal = rowData['source'];
        var relationVal = rowData['relation'];
        var targetVal = rowData['target'];

        $('#modalDialog2 input[name="' + this.fieldNames['id'] + '"]').val(idVal);
        $('#modalDialog2 select[name="' + this.fieldNames['source'] + '"]').val(sourceVal);
        $('#modalDialog2 select[name="' + this.fieldNames['relation'] + '"]').val(relationVal);
        $('#modalDialog2 select[name="' + this.fieldNames['target'] + '"]').val(targetVal);
      },
      duplicate: {"field": "target", "errorMsg": "This relationship already exists."},
      addBtnId: 'addItemBtn2',
      dialogId: 'modalDialog2',
      formId: 'modalForm2',
      tableId: 'nodeTable2',
      modalInit: function() {
        $('#modalDialog2 select[name="' + this.fieldNames['source'] + '"]').val($('#hidId').val());
      },
      validate: function() {
        var $source = $('#modalDialog2 #' + this.fieldNames['source']);
        var $target = $('#modalDialog2 #' + this.fieldNames['target']);
        if($source.val() != $('#hidId').val() && $target.val() != $('#hidId').val()) {
          $source.parents('.formItem').filter(':first').addClass("inError");
          $('#msg-' + this.fieldNames['source']).html('Either the source or target must be the current model.');
          $source.attr('aria-invalid', 'true');
          $source.focus();
          return false;
        }
        return true;
      }
    };

    NodeMgr2.intialize(roptions);
    
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
    
      var NodeMgrMfr = new NodeManager();
    
      var optionsMfr = {
        fieldNames: {"id":"hidId","name":"txtName","url":"txtUrl"},
        itemName: 'manufacturer',
        addBtnId: 'addMfrBtn',
        dialogId: 'modalDialogMfr',
        formId: 'modalFormMfr',
        hasTable: false,
        duplicate: {"field": "name", "errorMsg": "That manufacturer already exists"},
        successCallback: function(addedItem) {
          //add manufacturer to the dropdown. set the selected option to the last added manufacturer.
          $('#' + opts.fieldNames['mfr'] + ' option').first().after('<option value="' + addedItem['id'] + '">' + Util.htmlEncode(addedItem['name']) + '</option');
          $('#' + opts.fieldNames['mfr']).val(addedItem['id']);
          $('#' + opts.fieldNames['mfr']).focus();
        }
      }

      NodeMgrMfr.intialize(optionsMfr);

      var NodeMgrRelation = new NodeManager();

      var optionsRelation = {
        fieldNames: {"id":"hidId","name":"txtName"},
        itemName: 'relation',
        addBtnId: 'addRelationBtn',
        dialogId: 'modalDialogRelation',
        formId: 'modalFormRelation',
        hasTable: false,
        duplicate: {"field": "name", "errorMsg": "That relation already exists"},
        successCallback: function(addedItem) {
          //add relation to the dropdown. set the selected option to the last added relation.
          $('#' + roptions.fieldNames['relation'] + ' option').first().after('<option value="' + addedItem['id'] + '">' + Util.htmlEncode(addedItem['name']) + '</option');
          $('#' + roptions.fieldNames['relation']).val(addedItem['id']);
          $('#' + roptions.fieldNames['relation']).focus();
        }
      }

      NodeMgrRelation.intialize(optionsRelation);
      
      //todo: replace these handlers with a common reusable function.
      $('#' + optionsRelation.dialogId).on('shown', function() {
        //increase the z-index of the modal and its backdrop +20
        $(this).css("z-index", $(this).css("z-index") + 20);
        $('.modal-backdrop').last().css("z-index", $('.modal-backdrop').css("z-index") + 20);
        $('.modal-backdrop').first().hide();
      });

      $('#' + optionsRelation.dialogId).on('hidden', function() {
        $('.modal-backdrop').first().show();
      });
    }
    
  });
});
