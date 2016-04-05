define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    //get model details based on current selected model.
    if($('#selModel').val() != '') {
      updateModelDetails($('#selModel').val());
    }
    
    //event handler on model dropdown change, change the model details.
    $('#selModel').change(function(e){
      updateModelDetails($(this).val());
    });
    
    function updateModelDetails(model) {
      
      $('#modelDetails').remove();
      
      if(model == '') {
        return;
      }
      
      $('#msg-selModel').after('<div class="modelDetails speech-bubble speech-bubble-left" id="modelDetails"><div class="col1"><br />Loading model details <img class="vertical-align-children-middle" id="loading" src="' + Util.rootdir + 'cdn/images/loading_dark.gif" alt="loading animation" /><br /><br /></div></div>');
      
      //get model details from database using ajax.
      //at least four queries. one for model details. one for categories. one for relationships. one for custom attributes.
      var modelDetails = {};
      modelDetails.categories = [];
      modelDetails.relationships = [];
      modelDetails.attributes = [];
      modelDetails.attachments = [];
      
      var csrfTok = $('#csrfToken').html();
      
      $.when(
      
      // Model Details
      
      $.ajax({
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': csrfTok},
        data: {
          model: 'AssetModel',
          method: 'getAll',
          filter: {
            cols: [{col: 'id', val: model}]
          }
        },
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#modelDetails').html('<div class="col1 errorText">I\'m sorry.<br />Something went wrong.<br />Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>');
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          $.each(data['result']['value']['items'], function(index, value) {
            modelDetails.url = value['url'];
            modelDetails.desc = value['desc'];
            modelDetails.img = value['img'];
            modelDetails.img_modified = value['img_modified'];
          });
        }
      }),
      
      // Categories
      
      $.ajax({
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': csrfTok},
        data: {
          model: 'AssetModelCategory',
          method: 'getAll',
          filter: {
            cols: [{col: 'model', val: model}]
          }
        },
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#modelDetails').html('<div class="col1 errorText">I\'m sorry.<br />Something went wrong.<br />Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>');
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          $.each(data['result']['value']['items'], function(index, value) {
            modelDetails.categories.push({id: value['category'], category: value['Category_name']});
          });
        }
      }),
      
      // Relationships
      
      $.ajax({
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': csrfTok},
        data: {
          model: 'AssetModelRelation',
          method: 'getAll',
          filter: {
            cols: [{ 
              col: 'source',
              val: model
            },
            { 
              col: 'target',
              val: model
            }],
            separator: 'OR'
          }
        },
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#modelDetails').html('<div class="col1 errorText">I\'m sorry.<br />Something went wrong.<br />Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>');
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          $.each(data['result']['value']['items'], function(index, value) {
            modelDetails.relationships.push({sourceId: value['source'], source: value['Manufacturer_name'] + ' ' + value['AssetModel_model'], relation: value['Relation_name'], targetId: value['target'], target: value['Manufacturer2_name'] + ' ' + value['AssetModel2_model']});
          });
        }
      }),
      
      // Attributes
      
      $.ajax({
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': csrfTok},
        data: {
          model: 'AssetModelAttribute',
          method: 'getAll',
          filter: {
            cols: [{ 
              col: 'model',
              val: model
            }]
          },
          order: [{colClass: 'Attribute', col: 'name'}, {col: 'id'}]
        },
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#modelDetails').html('<div class="col1 errorText">I\'m sorry.<br />Something went wrong.<br />Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>');
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          var unitTxt = '';
          var prevName = '';
          var attrNum = 1;
          var attrNumTxt = '';
          
          $.each(data['result']['value']['items'], function(index, value) {
            attrNumTxt = '';
            if(value['Attribute_name'] == prevName) {
              attrNum++;
              attrNumTxt = ' #' + attrNum;
            }
            else if(index < (data['result']['value']['items'].length - 1) && value['Attribute_name'] === data['result']['value']['items'][index + 1]['Attribute_name']) {
              attrNum = 1;
              attrNumTxt = ' #' + attrNum;
            }
            else {
              attrNum = 1;
            }
            prevName = value['Attribute_name'];
            
            unitTxt = value['AttributeType_abbr'];
          
            if(unitTxt == null) {
              if(value['Attribute_type'] > 4) {
                unitTxt = ' ' + value['AttributeType_unit'];
              }
              else {
                unitTxt = '';
              }
            }
            
            modelDetails.attributes.push({name: value['Attribute_name'] + attrNumTxt, value: value['value'], unit: unitTxt});
          });
        }
      }),
      
      // Attachments
      
      $.ajax({
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': csrfTok},
        data: {
          model: 'AssetModelAttachment',
          method: 'getAll',
          filter: {
            cols: [{col: 'model', val: model}]
          }
        },
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#modelDetails').html('<div class="col1 errorText">I\'m sorry.<br />Something went wrong.<br />Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>');
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          $.each(data['result']['value']['items'], function(index, value) {
            modelDetails.attachments.push({name: value['name'], num: value['num'], extension: value['extension']});
          });
        }
      })
      
      ).done(function(mod, cat, rel, att, atm){
        
        $('#modelDetails').html('<div class="col1">Model details loaded.</div>');
        var modHTML = '<div class="col1">';
        
        if(modelDetails.img !== null){
          modHTML += '<a href="' + Util.rootdir + 'uploads/images/assetmodels/' + model + '.' + modelDetails.img + '?ts=' + (new Date(modelDetails.img_modified).getTime() / 1000) + '"><img class="thumbImg" src="' + Util.rootdir + 'uploads/images/assetmodels/thumbs/' + model + '.' + modelDetails.img + '?ts=' + (new Date(modelDetails.img_modified).getTime() / 1000) + '" alt="' + Util.htmlEncode(modelDetails.desc) + '"/></a><br />';
        }
        else {
          modHTML += '<div class="noImage">No Image</div>';
        }
        
        if(modelDetails.url !== null) {
          modHTML += '<a href="' + Util.htmlEncode(modelDetails.url) + '" target="_blank">Website</a>';
        }
        modHTML += '</div>';
        modHTML += '<div class="col2"><h4>' + Util.htmlEncode(modelDetails.desc) + '</h4>';
        $.each(modelDetails.attributes, function(index, value) {
          modHTML += '<p>' + Util.htmlEncode(value['name']) + ':<span class="big-bold-italic"> ' + Util.htmlEncode(value['value']) + Util.htmlEncode(value['unit']) + '</span></p>';
        });
        modHTML += '<p>Categories: ';
        if(modelDetails.categories.length > 0) {
          $.each(modelDetails.categories, function(index, value) {
            modHTML += '<a href="' + Util.rootdir + 'assets?item=category&value=' + value['id'] + '">' + Util.htmlEncode(value['category']) + '</a>, ';
          });
          modHTML = modHTML.substring(0, modHTML.length - 2) + '';
        }
        else {
          modHTML += '<span class="big-bold-italic">None</span>';
        }
        modHTML += '</p>';
        $.each(modelDetails.relationships, function(index, value) {
          //make source and targets links to assets?item=#&value=#
          modHTML += '<p><a href="' + Util.rootdir + 'assets?item=model&value='+ value['sourceId'] + '">' + Util.htmlEncode(value['source']) + '</a> <span class="bold-italic">' + Util.htmlEncode(value['relation']) + '</span> <a href="' + Util.rootdir + 'assets?item=model&value='+ value['targetId'] + '">' + Util.htmlEncode(value['target']) + '</a></p>';
        });
        modHTML += '<p>Attachments: ';
        if(modelDetails.attachments.length > 0) {
          $.each(modelDetails.attachments, function(index, value) {
            modHTML += '<a href="' + Util.rootdir + 'download?item=model&value=' + model + '&num=' + value['num'] + '&extension=' + value['extension'] + '&name=' + value['name'] + '">' + value['name'] + '.' + value['extension'] + '</a>, ';
          });
          modHTML = modHTML.substring(0, modHTML.length - 2);
        }
        else {
          modHTML += '<span class="big-bold-italic">None</span>';
        }
        modHTML += '</p>';
        modHTML += '</div>';
        
        $('#modelDetails').html(modHTML);
      });
    }// end updateModelDetails()
    
    if(Util.currentPage() != 'assets/new') {
    
      var NodeMgr = new NodeManager();

      var options = {
        itemName: 'asset',
        modelName: 'Asset',
        itemVal: $('#hidId').val(),
        postURL: 'assets/' + $('#hidId').val(),
        successFunction: function(data, textStatus, jqXHR) {
          window.location = '../assets' + querystring;
        }
      }
      NodeMgr.confirmDelete(options);

      options = {
        modals: false,    
        fieldNames: [{"asset":"hidAssetIdCO","location":"selLocationCO","purpose":"txtPurposeCO","finish":"txtFinishCO","notes":"txtaNotesCO"}, {"asset":"hidAssetIdCI","notes":"txtaNotesCI"}, {"asset":"hidAssetIdR","purpose":"txtPurposeR","notes":"txtaNotesR"}, {"asset":"hidAssetIdUR","notes":"txtaNotesUR"}, {"id":"hidId","model":"selModel","location":"selLocation","serial":"txtSerial","notes":"txtaNotes"}],
        limit: 10,
        filter: {
          cols: [{ 
            col: 'asset',
            val: $('#hidId').val()
          }]
        },
        order: [{
          col: 'time',
          dir: 'DESC'
        }],
        itemName: 'transaction',
        getCallback: function(retVal, setFocus) {
          
          var lastTransaction = retVal['items'][0];
          
          if(retVal['count'] != 0 && lastTransaction.TransactionType_name == 'Check-out') {
            //checked-out to me
            if(lastTransaction.User_uid == $('html').attr('data-user')) {

              var dueTitleTxt = '';
              if(lastTransaction.finish !== null) {
                dueTitleTxt = Util.htmlEncode($.format.date(Util.parseDate(lastTransaction.finish).toString(), 'M/d/yyyy'));
              }

              $('#actionsSubSection').html('<button type="button" name="checkIn" data-asset="' + $('#hidId').val() + '" value="checkIn" id="checkIn" class="btn btn-warning" title="Due: ' + dueTitleTxt + '"><i class="icon-white icon-upload"></i> Check-in</button>');
            }
            //checked-out to someone else.
            else {
              var nameText2 = '';

              nameText2 = '<a href="' + Util.rootdir + 'assets?item=user&value=' + Util.htmlEncode(lastTransaction.User_uid) + '">' + Util.htmlEncode(lastTransaction.User_name) + '</a>';
              if(lastTransaction.User_email != null) {
                nameText2 += ' <a href="mailto:' + Util.htmlEncode(lastTransaction.User_email) + '" aria-label="send email to user"><i class="icon-envelope"></i></a>';
              }
              
              $('#actionsSubSection').html('Out to ' + nameText2);
            }
          }
          else if(retVal['count'] != 0 && lastTransaction.TransactionType_name == 'Restrict') {
            if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {

              $('#actionsSubSection').html('<button type="button" name="unRestrict" data-asset="' + $('#hidId').val() + '" value="unRestrict" id="unRestrict" class="btn btn-warning"><i class="icon-white icon-ok-circle"></i> Unrestrict</button>');
            }
            else {
              $('#actionsSubSection').html('Restricted');
            }
          }
          else {
            if($('html').attr('data-role') > ISLE_VIEWER) {
              var restrictBtn = '';
              if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
                restrictBtn = ' <button type="button" name="restrict" data-asset="' + $('#hidId').val() + '" value="restrict" id="restrict" class="btn"><i class="icon-remove-circle"></i> Restrict</button>';
              }

              $('#actionsSubSection').html('<button type="button" name="checkOut" data-asset="' + $('#hidId').val() + '" value="checkOut" id="checkOut" class="btn"><i class="icon-download"></i> Check-out</button>' + restrictBtn);
            }
          }
          if(setFocus) {
            $('#actionsSubSection button').first().focus();
          }
        },
        buildTable: function(retVal) {

          var tableHTML = '<thead><th>Time</th><th>Action</th><th>User</th><th>Location</th><th>Purpose</th><th>Est. Finish Date</th><th>Notes</th></thead><tbody>';
          var nameText = '';
           
          $.each(retVal['items'], function(index, val){
            tableHTML += '<tr><td>' + Util.htmlEncode($.format.date(val['time'], 'M/d/yyyy h:mm a')) + '</td>';
            tableHTML += '<td>' + Util.htmlEncode(val['TransactionType_name']) + '</td>';

            if(val['User_email'] == null) {
              nameText = Util.htmlEncode(val['User_name']);
            }
            else {
              nameText = '<a href="mailto:' + Util.htmlEncode(val['User_email']) + '">' + Util.htmlEncode(val['User_name']) + '</a>';
            }
            
            tableHTML += '<td>' + nameText + '</td>';
            tableHTML += '<td>' + Util.htmlEncode(val['Location_center']) + ' ' + Util.htmlEncode(val['Location_bldg']) + '-' + Util.htmlEncode(val['Location_room']) + '</td>';
            tableHTML += '<td>' + Util.htmlEncode(val['purpose']) + '</td>';
            if(!val['finish'] || val['finish'].indexOf('00') == 0) {
              tableHTML += '<td></td>';
            }
            else {
              tableHTML += '<td>' + Util.htmlEncode($.format.date(Util.parseDate(val['finish']).toString(), 'M/d/yyyy')) + '</td>';
            }
            tableHTML += '<td>' + Util.htmlEncode(val['notes']) + '</td></tr>';
          });

          tableHTML += '</tbody>'
          $('#nodeTable').html(tableHTML);
        }
      }

      NodeMgr.intialize(options);

      $( "#" + options.fieldNames[0].finish ).datepicker({
        showOn: "button",
        buttonImage: Util.rootdir + "cdn/images/calendar.gif"
      });

      $("#transData").slideUp(1);
      // Expand or collapse:
      $("#VerColMenu li h4").on('click keypress', function(e){
        if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
          $(this).children('span.expander').html() === "+" ? $(this).children('span.expander').html("-") : $(this).children('span.expander').html("+");
          $(this).next().slideToggle("fast");
        }
      });
    }
    
    if($('html').attr('data-role') >= ISLE_CONTRIBUTOR) {
    
      var NodeMgrLocation = new NodeManager();
    
      var optionsLocation = {
          fieldNames: {"id":"id","center":"selCenter","bldg":"txtBldg","room":"txtRoom"},
          itemName: 'location',
          addBtnId: 'addLocationBtn',
          dialogId: 'modalDialogLocation',
          formId: 'modalFormLocation',
          hasTable: false,
          duplicate: {"field": "room", "errorMsg": "This location already exists"},
          successCallback: function(addedItem) {
            //add location to the dropdown. set the selected option to the last added location.
            $('#selLocation option').first().after('<option value="' + addedItem['id'] + '">' + Util.htmlEncode(addedItem['center']) + ' ' + Util.htmlEncode(addedItem['bldg']) + '-' + Util.htmlEncode(addedItem['room']) + '</option');
            $('#selLocation').val(addedItem['id']);
            $('#selLocation').focus();
          }
        }

        NodeMgrLocation.intialize(optionsLocation);

        var opts = {
          fieldNames: optionsLocation.fieldNames,
          itemName: optionsLocation.itemName,
          select: ['center'],
          filter: {
            cols: []
          }
        }

        $('#' + optionsLocation.fieldNames['center']).autocomplete(NodeMgrLocation.buildACOptions(opts));

        var opts2 = {
          fieldNames: optionsLocation.fieldNames,
          itemName: optionsLocation.itemName,
          select: ['bldg'],
          filter: {
            cols: ['center']
          }
        }

        $('#' + optionsLocation.fieldNames['bldg']).autocomplete(NodeMgrLocation.buildACOptions(opts2));

        var opts3 = {
          fieldNames: optionsLocation.fieldNames,
          itemName: optionsLocation.itemName,
          select: ['room'],
          filter: {
            cols: ['center','bldg']
          }
        }

        $('#' + optionsLocation.fieldNames['room']).autocomplete(NodeMgrLocation.buildACOptions(opts3));
    }
  });
});