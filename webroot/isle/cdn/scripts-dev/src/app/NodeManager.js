define(["jquery", "./Util"], function($, Util) {
  
  var NodeManager = function() {
    
    this.intialize = function(options) {
      var that = this;

      that.props = {};

      var defaults = {
        modelName: Util.capitalize(options.itemName),
        itemNamePlural: options.itemName + 's',
        modals: true,
        select: [],
        distinct: false,
        filter: null,
        order: [],
        start: 0,
        limit: 25,
        getCallback: function(){return;},
        duplicate: {"field": null, "errorMsg": null},
        validate: function(){return true;},
        tree: false,
        modalInit: function(){return;},
        addBtnId: 'addItemBtn',
        dialogId: 'modalDialog',
        formId: 'modalForm',
        tableId: 'nodeTable',
        urlVars: undefined,
        savedState: undefined,
        rowsClickable: true,
        rowClickHandler: function(){return true;},
        firstFocus: 'input:text',
        hasTable: true,
        successCallback: function(){return;},
        selectItems: false
      };

      if(typeof options == 'object') {
        options = $.extend(defaults, options);
      }
      else {
        options = defaults;
      }

      that.props.options = options;
      that.props.element = undefined;
      that.props.api = undefined;
      that.props.retVal = undefined;
      that.props.callback = true;
      that.props.callbackSetFocus = false;
      that.props.start = options.start;
      that.props.limit = options.limit;
      that.props.csrfTok = $('#csrfToken').html();

      //trap input inside the modal
      $(".modal").trap();

      // add item button handler
      if(options.modals) {
        $('#' + that.props.options.addBtnId).click(function(e){
          that.showDialog('add', e.currentTarget);
        });
      }
      
      if(that.props.options.hasTable) {
        //if urlvars param isn't set
        if(typeof options.urlVars == 'undefined') {
          window.onpopstate = function(event){that.stateChange(event)};

          that.props.options.urlVars = {};
          that.props.options.savedState = {};

          that.props.options.urlVars.page = 1;
          that.props.options.savedState.page = 1;

          if(parseInt(Util.getParameterByName('page')) > 0) {
            that.props.options.urlVars.page = parseInt(Util.getParameterByName('page'));
            that.props.options.savedState.page = parseInt(Util.getParameterByName('page'));
          }
        }
        else {
          that.props.options.urlVars.page = parseInt(that.props.options.urlVars.page) > 0 ? parseInt(that.props.options.urlVars.page) : 1;
        }

        that.getItems();

        //pagination button event handlers
        $('#prevPageBtn').on('click keypress', function(e){
          if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
            if(!$('#prevPageBtn').hasClass('disabled')) {
              that.props.options.urlVars.page = parseInt(that.props.options.urlVars.page) > 0 ? parseInt(that.props.options.urlVars.page) : 1;
              that.props.options.urlVars.page--;
              if(Modernizr.history) {
                that.props.options.savedState.page = parseInt(that.props.options.savedState.page) > 0 ? parseInt(that.props.options.savedState.page) : 1;
                that.props.options.savedState.page--;
                that.props.options.savedState.urlVars = that.props.options.urlVars;
                window.history.pushState(that.props.options.savedState, "", that.props.options.itemNamePlural + Util.createQueryString(that.props.options.urlVars));
                that.getItems();
              }
              else {
                window.location = Util.rootdir + that.props.options.itemNamePlural + Util.createQueryString(that.props.options.urlVars);
              }
            }
          }
        });

        $('#nextPageBtn').on('click keypress', function(e){
          if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
            if(!$('#nextPageBtn').hasClass('disabled')) {
              that.props.options.urlVars.page = parseInt(that.props.options.urlVars.page) > 0 ? parseInt(that.props.options.urlVars.page) : 1;
              that.props.options.urlVars.page++;
              if(Modernizr.history) {
                that.props.options.savedState.page = parseInt(that.props.options.savedState.page) > 0 ? parseInt(that.props.options.savedState.page) : 1;
                that.props.options.savedState.page++;
                that.props.options.savedState.urlVars = that.props.options.urlVars;
                window.history.pushState(that.props.options.savedState, "", that.props.options.itemNamePlural + Util.createQueryString(that.props.options.urlVars));
                that.getItems();
              }
              else {
                window.location = Util.rootdir + that.props.options.itemNamePlural + Util.createQueryString(that.props.options.urlVars);
              }
            }
          }
        });
      }

      // Set the focus on the first text input in the dialogs.
      if(options.modals) {
        $('#' + that.props.options.dialogId).on('shown', function(){
          $('#' + that.props.options.dialogId + ' ' + that.props.options.firstFocus + ':visible:first').focus().select();
        });
      }
      
      // message close handler
      $('.listRight').on('click keypress', '#userMessage .close', function(e){
        if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
          e.preventDefault();
          $('#userMessage').fadeOut(150);
          $('#userMessage').hide();
          $('.scroll-pane').removeClass('has-message');
          $('#' + that.props.options.addBtnId).focus();
        }
      });
      
      if(that.props.options.selectItems) {
        // checkall handler
        $('#container').on("click", ".checkall", function(e){
          if(e.target.nodeName == 'TH') {
            var $input = $(this).children('input').first();
            $input.prop('checked', !$input.prop('checked'));
            $(this).toggleClass('cc-checked', $input.prop('checked'));
          }
          else {
            var $input = $(this);
            $input.parent('th.checkall').toggleClass('cc-checked', $input.prop('checked'));
          }
          $input.parents('table:eq(0)').find(':checkbox').prop('checked', $input.prop('checked'));
          $input.parents('table:eq(0)').find('td.checkone').toggleClass('cc-checked', $input.prop('checked'));
          updateAssetActions();
          e.stopPropagation();
        });

        // checkone handler
        $('#container').on("click", '.checkone', function(e){
          //if surrounding td was clicked check the checkbox.
          if(e.target.nodeName == 'TD') {
            var $input = $(this).children('input').first();
            $input.prop('checked', !$input.prop('checked'));
            $(this).toggleClass('cc-checked', $input.prop('checked'));
          }
          else {
            $(this).parent('td.checkone').toggleClass('cc-checked', this.checked);
          }
          updateAssetActions();
          e.stopPropagation();
        });

        function updateAssetActions() {
          //get all the selected checkboxes and get their asset ids.
          //use retVal to lookup the state of each selected asset.
          //show the appropriate action depending on what assets are selected. (check-out or check-in or none).
          //if all selected are checked out to me (at the same location?) I can check them in.
          //if all selected are available for check-out I can check them out.
          
          var checkOut = false;
          var checkIn = false;
          var numSelected = 0;
          var val = null;
          var selectedAssets = [];
          
          $("input.checkone:checkbox:checked").each(function(index, element){
            numSelected++;
            val = Util.getObjects(that.props.retVal['items'], 'id', element.value)[0];
            selectedAssets.push(element.value);
            
            if(index == 0) {
              checkOut = true;
              checkIn = true;
            }
            
            if(val['Transaction_type'] == 1) { //checked out
              if(val['Transaction_User_uid'] == $('html').attr('data-user')) { //checked-out to me
                checkOut = false;
              }
              else { // checked-out to someone else
                checkIn = false;
                checkOut = false;
              }
            }
            else if(val['Transaction_type'] == 3) {
              checkIn = false;
              checkOut = false;
            }
            else {
              checkIn = false;
            }
          });
          
          if(checkOut){
            //show checkout button with numSelected icon.
            $('.asset-actions #checkOutMultBtn').removeClass('hide');
            $('.asset-actions #checkInMultBtn, .asset-actions .alert').addClass('hide');
            $('.asset-actions #checkOutMultBtn').attr('data-asset', selectedAssets.join(','));
            $('.asset-actions #checkOutMultBtn .badge').html(numSelected);
          }
          else if(checkIn){
            //show checkin button with numSelected icon.
            $('.asset-actions #checkInMultBtn').removeClass('hide');
            $('.asset-actions #checkOutMultBtn, .asset-actions .alert').addClass('hide');
            $('.asset-actions #checkInMultBtn').attr('data-asset', selectedAssets.join(','));
            $('.asset-actions #checkInMultBtn .badge').html(numSelected);
          }
          else {
             $('.asset-actions #checkInMultBtn, .asset-actions #checkOutMultBtn').addClass('hide');
             //if at least one item is selected show the message, "No actions available for items selected."
             if(numSelected > 0) {
               $('.asset-actions .alert').removeClass('hide');
             }
             else {
               $('.asset-actions .alert').addClass('hide');
             }
          }
        }
      }
      
    }// end this.initialize()

    this.resetInputs = function(whichModal) {
      if(typeof whichModal == 'undefined') {
        whichModal = '';
      }
      $('#' + this.props.options.dialogId + whichModal + ' input[type="text"], #' + this.props.options.dialogId + whichModal + ' textarea').val('');
      $('#' + this.props.options.dialogId + whichModal + ' select').val($('#' + this.props.options.dialogId + whichModal + ' select option:first').val());
      $('#' + this.props.options.dialogId + whichModal + ' input.ui-widget').val('');
      $('#' + this.props.options.dialogId + whichModal + ' input[type="checkbox"]').attr('checked', false);
      this.removeErrors();
    }

    this.removeErrors = function() {
      //remove all inError classes from formItems, set html of .err to blank
      $('.formItem').removeClass('inError');
      $('.err').html('&nbsp;');
    }

    this.addError = function(context, fieldName, errorMsg, setFocus) {
      var $formField = $(context + ' [name="' + fieldName + '"]').filter(':first');
      var fi = $formField.parents('.formItem').filter(':first');
      fi.addClass("inError");
      var msgElement = fi.find('.err').filter(':first');
      //todo: html encode before adding to html. what about the manufacturer url error where there are links in the error message.
      msgElement.html(errorMsg);
      $formField.attr('aria-invalid', 'true');
      $formField.attr('aria-describedby', msgElement.attr('id'));
      if(setFocus) {
        $formField.focus();
      }
    }
    
    this.getItems = function(idOfClicked) {
      var that = this;
      if(that.props.options.hasTable) {
        if(that.props.options.selectItems) {
          //hide the buttons.
          $('.asset-actions button, .asset-actions .alert').addClass('hide');
        }
        
        var options = that.props.options;

        //set start based on page.
        that.props.options.urlVars.page = parseInt(that.props.options.urlVars.page) > 0 ? parseInt(that.props.options.urlVars.page) : 1;
        that.props.start = that.props.options.urlVars.page * that.props.limit - that.props.limit;
        
        //if user clicks the previous page button before the last has finished they can make start < 0. this fixes that.
        if(that.props.start < 0) {
          that.props.start = 0;
        }

        //disable the pagination buttons.
        $('#prevPageBtn, #nextPageBtn').addClass('disabled').attr('aria-disabled','true');
        
        //show a loading animation.
        $('#' + that.props.options.tableId).html('<thead><tr><td>Loading items <img class="vertical-align-children-middle" id="loading" src="' + Util.rootdir + 'cdn/images/loading.gif" alt="loading animation" /></td></tr></thead>');

        // get all items and populate the table.
        $.ajax({
          url: Util.rootdir + 'remoteInterface',
          headers: {'x-csrftoken': that.props.csrfTok},
          data: {model: options.modelName, method: 'getAll', start: that.props.start, limit: that.props.limit, select: options.select, distinct: options.distinct, filter: options.filter, order: options.order, tree: options.tree},
          dataType: 'json',
          dataFilter: Util.parseJSON,
          error: function(jqXHR, textStatus, errorThrown) {
            //an error occurred on the server.
            $('#userMessage').remove();
            $('#' + that.props.options.tableId).html('');
            $('<div id="userMessage" class="alert alert-error" role="alert" aria-label="I\'m sorry. Something went wrong."><a class="close" href="#" role="button" aria-label="Close">&times;</a>I\'m sorry. Something went wrong (NodeManager.js:316 ' + errorThrown + '). Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>').hide().prependTo('.listRight').fadeIn(150);
            $('.scroll-pane').addClass('has-message');
            that.props.retVal = '';
            $('#addBtn').removeAttr("disabled");
            return false;
          },
          success: function(data, textStatus, jqXHR) {
            //todo: make this its own function.
            that.props.retVal = data['result']['value'];
            
            if(that.props.start == 0 && that.props.callback) {
              var runAgain = options.getCallback(that.props.retVal, that.props.callbackSetFocus);
              that.props.callback = false;
              that.props.callbackSetFocus = true;
            }

            try {
              var rowCount = that.props.retVal['count'];
            }
            catch(err) {
              $('#' + that.props.options.tableId).html('');
              //todo: refactor into a separate method.
              $('#userMessage').remove();
              //todo: make this work for asset details page.
              $('<div id="userMessage" class="alert alert-error" role="alert" aria-label="I\'m sorry. Something went wrong."><a class="close" href="#" role="button" aria-label="Close">&times;</a>I\'m sorry. Something went wrong (NodeManager.js:340 ' + err + ') . Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>').hide().prependTo('.listRight').fadeIn(150);
              $('.scroll-pane').addClass('has-message');
              throw err;
            }

            //populate table
            if(rowCount > 0) {
              options.buildTable(that.props.retVal);
            }

            // table striping for IE
            $('.table-striped tr:nth-child(2n+1)').addClass('odd');

            // highlight the row when cell receives keyboard focus.
            $('.table-clickable td').on('focusin focusout', function(e){
              $(this).parents('tr').first().removeClass('highlight');
              if(e.type === 'focusin') {
                $(this).parents('tr').first().addClass('highlight');
              }
            });

            //when returning from save and delete set focus on the appropriate element.
            if(typeof idOfClicked != 'undefined' && idOfClicked != '') {
              //if the last item on a page is deleted set focus on the last item of the previous page.
              if(idOfClicked == 'setToLast') {

                var focusCell = $('#' + that.props.options.tableId + ' tbody tr').last().children('td').first();

                if(focusCell.length > 0) {
                  //if the last item on a page is deleted set focus on the last item of the previous page.
                  $(focusCell).focus();
                }
                else {
                  //if the very last item is deleted set focus on the add item button.
                  $('#' + that.props.options.addBtnId).focus();
                }
              }
              else {
                //if returning from save action set focus to element that was edited.
                if($('td#' + idOfClicked).length > 0) {
                  $('td#' + idOfClicked).focus();
                }
                else {
                  $('#' + idOfClicked).focus();
                }
              }
            }

            // set the pagination numbers.
            var total = rowCount;
            var first = that.props.start + 1;
            var last = that.props.start + that.props.limit;

            if(first > total) { //happens when the last page has one item on it and it is deleted. or when the page url parameter is set to an out of bounds number.
              that.props.start = Math.floor(total / that.props.limit) * that.props.limit;
              if(that.props.start == total) {
                //when total is evenly divisible by limit.
                that.props.start -= that.props.limit;
              }

              //set urlvars.page and savedstate.page to the right value.
              that.props.options.urlVars.page = Math.ceil(total / that.props.limit);
              that.props.options.savedState.page = that.props.options.urlVars.page;
              if (that.props.start < 0) { //when the only item is deleted
                that.props.start = 0; //set start to 0 so it doesn't get set to a negative number which would result in an error.
              }
              else {
                that.getItems(idOfClicked);//go to the previous page
              }
            }

            if(last > total) {//sets the last number correctly on the last page.
              last = total;
            }

            $('#firstItem').html(first);
            $('#lastItem').html(last);
            $('#totalItems').html(total);

            // pagination buttons. 
            $('#prevPageBtn, #nextPageBtn').removeClass('disabled').removeAttr('aria-disabled');

            if(first == 1) { //disable the previous page button on the first page
              $('#prevPageBtn').addClass('disabled').attr('aria-disabled','true');
            }
            if(last == total) { //disable the next page button on the last page.
              $('#nextPageBtn').addClass('disabled').attr('aria-disabled','true');
            }

            //show the page controls or hide them if there are no results.
            if(total == 0) {
              $('#listNav').css('visibility', 'hidden');
              $('#' + that.props.options.tableId).html('<thead><tr><td>No ' + options.itemNamePlural + ' found.</td></tr></thead>');
            }
            else {
              $('#listNav').css('visibility', 'visible');
            }

            // Handle the row click event
            $('#' + that.props.options.tableId + '.table-clickable tr').on('click', function(e){
              var doDefaultHandler = that.props.options.rowClickHandler(e);
              if(doDefaultHandler) {
                if($(this).find('span[role="button"]').length > 0 || $(this).find('a[id="itemSelector"]').length > 0) {
                  if(e.target.nodeName != 'INPUT' && e.target.nodeName != 'LABEL' && e.target.parentNode.nodeName != 'LABEL' && e.target.nodeName != 'BUTTON' && e.target.nodeName != 'A' && e.target.parentNode.nodeName != 'A') {
                    if(!$(e.target).hasClass('checkone')) {
                      that.rowClick(e);
                    }
                  }
                }
              }
            });

            // Handle the cell keyboard enter event.
            $('#' + that.props.options.tableId + '.table-clickable span[role="button"]').on('keypress', function(e){
              if(e.type == 'keypress' && e.which == 13) {
                that.rowClick(e);
              }
            });

            //Handle the button click event
            $('#' + that.props.options.tableId + ' button, #actionsSubSection button, .asset-actions button').on('click keypress', function(e){
              if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
                that.buttonClick(e);
              }
            });
          }//end ajax success function
        });//end ajax function
      }//end if(that.props.options.hasTable)
    }//end this.getItems()
    

    // Sets inputs to values of clicked item
    this.rowClick = function(e) {
      if(this.props.options.hasTable) {
        if(!this.props.options.rowsClickable) {
          return false;
        }
        //handle both row click and cell keyboard enter.
        var selectedRow = e.currentTarget;
        var selectedCell = $(selectedRow).find('span[role="button"]').first()[0];
        if($(e.currentTarget).prop('tagName') === 'SPAN') { // keyboard enter on edit icon.
          selectedRow = $(e.currentTarget).parents('tr').first()[0];
          selectedCell = e.currentTarget;
        }

        if(this.props.options.modals) {
          var retres = this.props.options.fillForm(this.props.retVal, selectedRow, selectedCell);
          if(retres !== false) {
            this.showDialog('edit', selectedCell, selectedRow);
          }
        }
      }
    }

    // Sets inputs to values of clicked item
    this.buttonClick = function(e) {
      var that = this;
      if(that.props.options.hasTable) {
        var options = that.props.options;

        switch(e.currentTarget.name) {
          case 'checkOut':
            var whichModal = '';
            var firstFocus = 'select';
            var ajaxModel = 'TransactionCheckout';
            var transVerb = 'checked-out';
            var fnames = options.fieldNames[0];
            break;
          case 'checkIn':
            var whichModal = '2';
            var firstFocus = 'textarea';
            var ajaxModel = 'TransactionCheckin';
            var transVerb = 'checked-in';
            var fnames = options.fieldNames[1];
            
            var labelText = $('#' + that.props.options.dialogId + whichModal + ' label[for="chkConfirmReturned"]').html();
            labelText = labelText.replace('I returned the items to', 'I returned the item to');
            $('#' + that.props.options.dialogId + whichModal + ' label[for="chkConfirmReturned"]').html(labelText);
            
            break;
          case 'restrict':
            var whichModal = '3';
            var firstFocus = 'input[type="text"]';
            var ajaxModel = 'TransactionRestrict';
            var transVerb = 'restricted';
            var fnames = options.fieldNames[2];
            break;
          case 'unRestrict':
            var whichModal = '4';
            var firstFocus = 'textarea';
            var ajaxModel = 'TransactionUnrestrict';
            var transVerb = 'unrestricted';
            var fnames = options.fieldNames[3];
            break;
        }
        
        $('#' + that.props.options.dialogId + ' .modal-header #modalTitle').html('Check-out Asset');
        $('#' + that.props.options.dialogId + '2 .modal-header #modalTitle').html('Check-in Asset');
        
        //button clicked on list page.
        
        //if there's more than one asset id in the data-asset attribute than set thisAsset.id to the data-asset attribute.
        
        var thisAsset = Util.getObjects(that.props.retVal['items'], 'id', $(e.currentTarget).attr('data-asset'))[0];
        var onList = true;
        var multiple = false;
        
        if($(e.currentTarget).attr('data-asset').split(',').length > 1){
          // multiple items
          multiple = true;
          thisAsset = {};
          thisAsset.id = $(e.currentTarget).attr('data-asset');
          //change title to "assets"
          $('#' + that.props.options.dialogId + whichModal +  ' .modal-header #modalTitle').html($('#' + that.props.options.dialogId + whichModal +  ' .modal-header #modalTitle').html() + 's');
        }

        if(typeof thisAsset == 'undefined') { //button clicked on details page.
          onList = false;
          thisAsset = {};
          thisAsset.id = $('#' + options.fieldNames[4]['id']).val();
          thisAsset.location = $('#' + options.fieldNames[4]['location'] + ' option[value="' + $('#' + options.fieldNames[4]['location']).attr('data-location') + '"]').html();
        }

        that.resetInputs(whichModal);
        $('#' + that.props.options.dialogId + whichModal + ' input[name="' + fnames['asset'] + '"]').val(thisAsset.id);

        if(e.currentTarget.name == 'checkIn') {
          if(multiple) {
            var labelText = $('#' + that.props.options.dialogId + whichModal + ' label[for="chkConfirmReturned"]').html();
            labelText = labelText.replace('I returned the item to', 'I returned the items to');
            $('#' + that.props.options.dialogId + whichModal + ' label[for="chkConfirmReturned"]').html(labelText);
            $('#retLocation').html('their home locations.');
          }
          else if(onList) {
            $('#retLocation').html(Util.htmlEncode(thisAsset.Location_center + ' ' + thisAsset.Location_bldg + '-' + thisAsset.Location_room));
          }
          else {
            $('#retLocation').html(Util.htmlEncode(thisAsset.location));
          }
        }

        //handle the cancel and close events
        $('#' + that.props.options.dialogId + whichModal + ' button.close, ' + '#' + that.props.options.dialogId + whichModal + ' button[name="cancel"]').on('click keypress', function(ev){
          if(ev.type == 'click' || (ev.type == 'keypress' && ev.which == 13)) {
            ev.preventDefault();//ie wasn't closing the modal on enter keypress. this fixed it.
            $('#' + that.props.options.dialogId + whichModal).modal('hide');
            e.currentTarget.focus();
          }
        });

        $('#' + that.props.options.dialogId + whichModal).on('shown', function() {
          $('#' + that.props.options.dialogId + whichModal + ' ' + firstFocus + ':visible:first').focus();
        });

        var $modalSubmit = $('#' + that.props.options.formId + whichModal + ' .modal-footer button[type="submit"]');

        // handle modal add, save, and delete click events
        $modalSubmit.unbind('click');
        $modalSubmit.unbind('keypress');
        $modalSubmit.on('click keypress', function(ev){
          if(ev.type == 'click' || (ev.type == 'keypress' && ev.which == 13)) {
            ev.preventDefault();//Prevents the form from submitting which produces a Firefox error. http://stackoverflow.com/questions/5545577/ajax-post-handler-causing-uncaught-exception

            //run client-side validation
            if(!that.props.options.validate()) {
              return false;
            }

            //if check-in was clicked then make sure checkbox was checked.
            if(e.currentTarget.name == 'checkIn') {
              if(!$('#chkConfirmReturned').attr('checked')) {
                $('#chkConfirmReturned').parents('.formItem').filter(':first').addClass("inError");
                $('#msg-chkConfirmReturned').html(' (This is required.)');
                $('#chkConfirmReturned').attr('aria-invalid', 'true');
                $('#chkConfirmReturned').focus();
                return false;
              }
            }

            //disable the buttons. show loading indicator.
            $("#" + that.props.options.dialogId + whichModal + " .modal-footer button").attr("disabled", "disabled");
            $("#" + that.props.options.dialogId + whichModal + " .modal-footer .footerRight").remove();
            $("#" + that.props.options.dialogId + whichModal + " .modal-footer").prepend('<span class="footerRight">Please wait <img class="vertical-align-children-middle" src="' + Util.rootdir + 'cdn/images/loading.gif" alt="loading animation"/></span>');

            // grab all input text, radio, checkbox, textarea, select
            var o = {};
            var a = $('#' + that.props.options.formId + whichModal).serializeArray();

            $.each(a, function() {        
              if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                      o[this.name] = [o[this.name]];
                }
                o[this.name].push($.trim(this.value) || '');
              }
              else {
                o[this.name] = $.trim(this.value) || '';
              }
            });

            $.ajax({
              url: Util.rootdir + 'remoteInterface',
              headers: {'x-csrftoken': that.props.csrfTok},
              type: 'POST',
              data: {model: ajaxModel, method: 'add', args: [o, fnames]},
              dataType: 'json',
              dataFilter: Util.parseJSON,
              error: function(jqXHR, textStatus, errorThrown) {
                //remove loading text and show an error in its place.
                // enable the buttons
                $("#" + that.props.options.dialogId + whichModal + " .modal-footer button").removeAttr("disabled");
                //replace loading text with error text.
                $("#" + that.props.options.dialogId + whichModal +
                  " .modal-footer .footerRight")
                    .addClass('errorText')
                    .html('I\'m sorry. Something went wrong (NodeManager.js:655 ' +
                                                errorThrown + ').');
                
                var retVal2 = '';
                return false;
              },
              success: function(data, textStatus, jqXHR) {
                var retVal2 = data['result']['value'];
                
                // reset the modal
                $("#" + that.props.options.dialogId + whichModal + " .modal-footer .footerRight").remove();
                that.removeErrors();

                var focusSet = false;

                //no errors so submit the form.
                if(data['result']['status'] === 'success') {
                  
                  // enable the buttons
                  $("#" + that.props.options.dialogId + whichModal + " .modal-footer button").removeAttr("disabled");

                  var idOfClicked = '';

                  // hide the modal
                  $('#' + that.props.options.dialogId + whichModal).modal('hide');
                  // show a message indicating success.
                  if(onList) {
                    $('#userMessage').remove();
                    
                    var plural = multiple ? 's' : '';
                    
                    $('<div id="userMessage" class="alert alert-success" role="alert" aria-label="Asset' + plural + ' ' + transVerb + ' successfully"><a class="close" href="#" role="button" aria-label="Close">&times;</a>Asset' + plural + ' ' + transVerb + ' successfully</div>').hide().prependTo('.listRight').fadeIn(150);
                    $('.scroll-pane').addClass('has-message');
                  }

                  idOfClicked = e.currentTarget.id;

                  //update the table.
                  that.props.callback = true;
                  that.getItems(idOfClicked);
                }
                else {// there were errors
                  var fi, formField, msgElement;
                  // Integrity Constraint violation
                  if(retVal2 == 'duplicate') {
                    alert("I'm sorry. Something went wrong (duplicate).");
                  }
                  // UI Exception
                  else {
                    //loop over retVal structure. find element with name that matches retVal.item name. Find the first parent with formItem class and add class inError. Inside the formItem class find the element with err class and set inner html to retVal.item value.
                    $.each(retVal2, function(key, val) {
                      if(!focusSet) {
                        that.addError('#' + that.props.options.formId + whichModal, fnames[key], val, true);
                        focusSet = true;
                      }
                      else {
                        that.addError('#' + that.props.options.formId + whichModal, fnames[key], val, false);
                      }
                    });
                  }
                  // enable the buttons
                  $("#" + that.props.options.dialogId + whichModal + " .modal-footer button").removeAttr("disabled");
                } //end if there are no errors.
              }//end success
            });//end ajax
          }
        });//end modal check-out button click event handler

        $("#" + that.props.options.dialogId + whichModal).modal('show');
      }
    }// end this.buttonClick()

    //sets up the dialog for either adding or editing an item
    this.showDialog = function(whichOne, selectedCell, selectedRow) {
      var that = this;
      var options = that.props.options;

      switch (whichOne) {
        case 'edit':
          // erase errors
          that.removeErrors();

          $("#" + that.props.options.dialogId + " .modal-header h3").html('Edit ' + Util.capitalize(options.itemName));
          $("#" + that.props.options.dialogId + " .modal-footer").html('<button type="submit" name="submit" value="Save" id="saveBtn" class="btn btn-primary">Save</button><button type="button" name="cancel" value="Cancel" id="cancelBtn" class="btn">Cancel</button><button type="button" name="delete" value="Delete" id="deleteBtn" class="btn btn-danger" data-dismiss="modal"><i class="icon-trash icon-white"></i> Delete</button>');
          break;
        case 'add':
          that.resetInputs();
          options.modalInit(that.props.retVal);
          $("#" + that.props.options.dialogId + " .modal-header h3").html('Add ' + Util.capitalize(options.itemName));
          $("#" + that.props.options.dialogId + " .modal-footer").html('<button type="submit" name="submit" value="Add" id="addBtn" class="btn btn-primary">Add</button><button type="button" name="cancel" value="Cancel" id="cancelBtn" class="btn" data-dismiss="modal">Cancel</button>');
          break;
      }

      //handle the cancel and close events
      $('#' + that.props.options.dialogId + '.modal button.close, ' + '#' + that.props.options.dialogId + '.modal button[name="cancel"]').on('click keypress', function(e){
        if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
          selectedCell.focus();
          e.preventDefault();//ie wasn't closing the modal on enter keypress. this fixed it.
          $('#' + that.props.options.dialogId).modal('hide');
        }
      });

      // handle modal add, save, and delete click events
      $('#' + that.props.options.formId + ' .modal-footer button[name="submit"], #' + that.props.options.formId + ' .modal-footer button[name="delete"]').click(function(e){
        e.preventDefault();//Prevents the form from submitting which produces a Firefox error. http://stackoverflow.com/questions/5545577/ajax-post-handler-causing-uncaught-exception

        if($(e.currentTarget).val() == 'Delete') {
          //are you sure?
          //make an ajax GET call to remoteinterface's getForeignKeyReferences.
          $.ajax({
            url: Util.rootdir + 'remoteInterface',
            headers: {'x-csrftoken': that.props.csrfTok},
            type: 'GET',
            data: {model: options.modelName, method: 'getForeignKeyReferences', nodeId: $('#' + options.fieldNames['id']).val()},
            dataType: 'json',
            dataFilter: Util.parseJSON,
            error: function(jqXHR, textStatus, errorThrown) {
              alert("I'm sorry. Something went wrong (NodeManager.js:772 " + errorThrown + ").");
              return false;
            },
            success: function(data, textStatus, jqXHR) {
              var retVal3 = data['result']['value'];
              var toBeDeleted = '';
              var toBeDeletedIntro = '';
              $.each(retVal3, function(key, val) {
                if(val > 0) {
                  toBeDeleted += '\n' + key + ': ' + val;
                }
              });

              if(toBeDeleted.length > 0) {
                toBeDeletedIntro = '\n\nYou will also be deleting the following:\n';
              }

              if(!confirm("Are you sure you want to delete this " + options.itemName + "?" + toBeDeletedIntro + toBeDeleted)) {
                return;
              }
              else {
                formSubmit();
              }
            }
          });
        }
        else {
          if(options.validate()) {
            formSubmit();
          }
        }

        function formSubmit() {
          //disable the buttons. show loading indicator.
          $("#" + that.props.options.dialogId + " .modal-footer button").attr("disabled", "disabled");
          $("#" + that.props.options.dialogId + " .modal-footer .footerRight").remove();
          $("#" + that.props.options.dialogId + " .modal-footer").prepend('<span class="footerRight">Please wait <img class="vertical-align-children-middle" src="' + Util.rootdir + 'cdn/images/loading.gif" alt="loading animation"/></span>');

          // grab all input text, radio, checkbox, textarea, select
          var o = {};
          var a = $('#' + that.props.options.formId).serializeArray();

          $.each(a, function() {        
            if (o[this.name] !== undefined) {
              if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
              }
              o[this.name].push($.trim(this.value) || '');
            }
            else {
              o[this.name] = $.trim(this.value) || '';
            }
          });

          switch ($(e.currentTarget).val()) {
            case 'Add':
              var saveMethod = 'add';
              var successMsg = Util.capitalize(options.itemName) + ' added successfully.';
              that.props.callback = true;
              break;
            case 'Save':
              var saveMethod = 'update';
              var successMsg = Util.capitalize(options.itemName) + ' saved successfully.';
              that.props.callback = true;
              break;
            case 'Delete':
              var saveMethod = 'delete';
              var successMsg = Util.capitalize(options.itemName) + ' deleted successfully.';
              that.props.callback = true;
              break;
          }
          
          //call the model object and pass in the form structure and the names of the fields.
          $.ajax({
            url: Util.rootdir + 'remoteInterface',
            headers: {'x-csrftoken': that.props.csrfTok},
            type: 'POST',
            data: {model: options.modelName, method: saveMethod, args: [o, options.fieldNames]},
            dataType: 'json',
            dataFilter: Util.parseJSON,
            error: function(jqXHR, textStatus, errorThrown) {
              if(saveMethod == 'delete') {
                alert("I'm sorry. Something went wrong (saveMethod == delete).");
              }
              else {
                // enable the buttons
                $("#" + that.props.options.dialogId + " .modal-footer button").removeAttr("disabled");
                //replace loading text with error text.
                $("#" + that.props.options.dialogId + " .modal-footer .footerRight")
                    .addClass('errorText')
                    .html('I\'m sorry. Something went wrong (NodeManager.js:862 ' +
                          errorThrown + ').');
              }
              var retVal2 = '';
              return false;
            },
            success: function(data, textStatus, jqXHR) {
              var retVal2 = data['result']['value'];

              $("#" + that.props.options.dialogId + " .modal-footer .footerRight").remove();
              that.removeErrors();

              var focusSet = false;

              //no errors so submit the form.
              if(data['result']['status'] === 'success') {
                // reset the modal
                $("#" + that.props.options.dialogId + " .modal-footer button").removeAttr("disabled");
                
                if(options.hasTable) {

                  var idOfClicked = '';
                  if(saveMethod == 'update') {
                    //set focus to id of updated element.
                    idOfClicked = $(selectedCell).attr('id');
                  }
                  else if(saveMethod == 'delete') {
                    //todo: set focus to first selectable row cell. for attribute types.
                    //set focus to the element prior to the one deleted.
                    idOfClicked = $(selectedRow).prev().children('td').first().attr('id');

                    if(typeof idOfClicked === 'undefined') {
                      //if their is no prior row, set focus to the next row.
                      idOfClicked = $(selectedRow).next().children('td').first().attr('id');
                      if(typeof idOfClicked === 'undefined') {
                        //if there is no next row, then the last item on the page was deleted so set focus on the last item of the previous page.
                        idOfClicked = 'setToLast';
                      }
                    }
                  }
                  else {
                    selectedCell.focus();
                  }
                }
                // hide the modal
                $('#' + that.props.options.dialogId).modal('hide');
                // show a message indicating success.
                if(options.hasTable) {
                  $('#userMessage').remove();
                  $('<div id="userMessage" class="alert alert-success" role="alert" aria-label="' + successMsg + '"><a class="close" href="#" role="button" aria-label="Close">&times;</a>' + successMsg + '</div>').hide().prependTo('.listRight').fadeIn(150);
                  $('.scroll-pane').addClass('has-message');
                }

                //update the table.
                if(options.hasTable) {
                  that.getItems(idOfClicked);
                }
                else {
                  options.successCallback(retVal2);
                }
              }
              else {// there were errors
                var fi, formField, msgElement;
                // Integrity Constraint violation
                if(retVal2 == 'duplicate') {
                  that.addError('#' + that.props.options.dialogId, that.props.options.fieldNames[options.duplicate.field], options.duplicate.errorMsg, true);
                }
                // UI Exception
                else {

                  //loop over retVal structure. find element with name that matches retVal.item name. Find the first parent with formItem class and add class inError. Inside the formItem class find the element with err class and set inner html to retVal.item value.
                  $.each(retVal2, function(key, val) {
                    if(!focusSet) {
                      that.addError('#' + that.props.options.dialogId, options.fieldNames[key], val, true);
                      focusSet = true;
                    }
                    else {
                      that.addError('#' + that.props.options.dialogId, options.fieldNames[key], val, false);
                    }
                  });
                }
                // enable the buttons
                $("#" + that.props.options.dialogId + " .modal-footer button").removeAttr("disabled");
              } //end if there are no errors.
            }//end success
          });//end ajax
        }//end formSubmit()
      });//end add, save, delete button click event handler

      // show the dialog.
      $("#" + that.props.options.dialogId).modal('show');
    }//end this.showDialog()

    this.confirmDelete = function(options) {
      var that = this;

      // handle delete click events
      $('#deleteBtn').on('click keypress', function(e){
        if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
          e.preventDefault();//Prevents the form from submitting which produces a Firefox error. http://stackoverflow.com/questions/5545577/ajax-post-handler-causing-uncaught-exception

          $.ajax({
            url: Util.rootdir + 'remoteInterface',
            headers: {'x-csrftoken': that.props.csrfTok},
            type: 'GET',
            data: {model: options.modelName, method: 'getForeignKeyReferences', nodeId: options.itemVal},
            dataType: 'json',
            dataFilter: Util.parseJSON,
            error: function(jqXHR, textStatus, errorThrown) {
              alert("I'm sorry. Something went wrong (NodeManager.js:971 " +
                    errorThrown + ").");
              return false;
            },
            success: function(data, textStatus, jqXHR) {
              var retVal3 = data['result']['value'];
              var toBeDeleted = '';
              var toBeDeletedIntro = '';
              $.each(retVal3, function(key, val) {
                if(val > 0) {
                  toBeDeleted += '\n' + key + ': ' + val;
                }
              });

              if(toBeDeleted.length > 0) {
                toBeDeletedIntro = '\n\nYou will also be deleting the following:\n';
              }

              if(!confirm("Are you sure you want to delete this " + options.itemName + "?" + toBeDeletedIntro + toBeDeleted)) {
                return;
              }
              else {
                var the_form = $('#deleteBtn').parents("form");
                var dataVal = the_form.serialize();
                var urlVal = the_form.attr( 'action' );
                var button = e.currentTarget;

                dataVal = dataVal + "&" + button.name + "=" + $(button).val() + '&fromJS=true';

                // Send the data using post
                $.ajax({
                  //firefox didn't like sending an empty url for some reason.
                  url: '../' + options.postURL,
                  type: 'POST',
                  data: dataVal,
                  dataType: 'json',
                  dataFilter: Util.parseJSON,
                  error: function(jqXHR, textStatus, errorThrown) {
                    alert("I'm sorry. Something went wrong (NodeManager.js:1009 " +
                          errorThrown + ").");
                  },
                  success: options.successFunction
                });
              }
            }
          });
        }
      }); 
    }

    this.buildACOptions = function(options) {
      var that = this;
      
      return {
        minLength: 1,
        source: function( request, response ) {
          var filter = {};
          filter.cols = [{ 
            col: (options.select.length == 2) ? options.select[1] : options.select[0],
            val: '%' + request['term'] + '%',
            operator: 'LIKE'
          }];

          $.each(options.filter.cols, function(index, value) {
            filter.cols.push({ 
              col: value,
              val: $('#' + options.fieldNames[value]).val()
            });
          });

          $.ajax({
            url: Util.rootdir + 'remoteInterface',
            headers: {'x-csrftoken': that.props.csrfTok},
            data: {
              model: Util.capitalize(options.itemName),
              method: 'getAll',
              select: options.select,
              distinct: true,
              filter: filter
            },
            dataType: 'json',
            dataFilter: Util.parseJSON,
            error: function(jqXHR, textStatus, errorThrown) {
              return false;
            },
            success: function(data, textStatus, jqXHR) {
              var rowCount = data['result']['value']['count'];
              var suggestions = [];
              $.each(data['result']['value']['items'], function(index, value) {
                if(options.select.length == 2) {
                  var temp = {
                    value: value[options.select[0]],
                    label: value[options.select[1]]
                  };
                  suggestions.push(temp);
                }
                else {
                  var temp = {
                    value: value[options.select[0]],
                    label: value[options.select[0]]
                  };
                  suggestions.push(temp);
                }

              });
              response( suggestions );
            }
          });
        }
      }
    }
    this.stateChange = function(event) {
      var that = this;
      if(that.props.options.hasTable) {
      
        //on page load this function runs after the document load function has finished.

        if(event.state) {
          that.props.options.urlVars.page = event.state.page;
          that.getItems();
        }
        else {
        //event.state will not be set when loading a page, or sometimes when going back or forward to pages with no url params. event.state will also not be set when you go back to a page you loaded manually using url params
          if(typeof that.props.retVal != 'undefined') {
            document.location.reload();
          }
        }
      }
    }
    
    return this;
  }
  
  return NodeManager;
});
