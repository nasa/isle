define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    //get url vars.
    var urlVars = {};
    var savedState = {};
    savedState.filter = [];
    urlVars.item = Util.getParameterByName('item');
    urlVars.value = Util.getParameterByName('value');
    urlVars.search = Util.getParameterByName('search');
    urlVars.page = Util.getParameterByName('page');
    var $selectedCat = false;  
    
    //whitelist validate the item. and make sure value is an int.
    if(urlVars.item === 'location' || (urlVars.item === 'model' || urlVars.item === 'category' || urlVars.item === 'manufacturer' || urlVars.item === 'user') && parseInt(urlVars.value)) {
      
      if(urlVars.item === 'category') {
        //category filter
        //go down through dom and collect the data-id attributes of descendant categories.
        $selectedCat = $('#VerColMenu span.selector[data-id="' + urlVars.value + '"]');
        $selectedCat.addClass('bold-italic');
        var family = $selectedCat.closest('li').find('.selector');
        savedState.category = urlVars.value;
        savedState.filter[0] = getCategoryFilter(family);
        
      }
      else if(urlVars.item === 'manufacturer') {
        savedState.filter[0] = {
          cols: [{colClass: 'AssetModel', col: 'mfr', val: parseInt(urlVars.value)}]
        }
      }
      else if(urlVars.item === 'user') {
        savedState.filter[0] = {
          cols: [{colClass: 'User', col: 'uid', val: parseInt(urlVars.value)}]
        }
      }
      else if(urlVars.item === 'location') {
        //todo: validate the url value var.
        // separate on comma to get the center, bldg, and room.
        
        urlVars.value = urlVars.value.split(",");
        var tmpFilterCols = [];
        var tmpCol = '';
        $.each(urlVars.value, function(index, value){
          switch(index){
            case 0:
              tmpCol = 'center';
              break;
            case 1:
              tmpCol = 'bldg';
              break;
            case 2:
              tmpCol = 'room';
          }
          tmpFilterCols.push({colClass: 'Location', col: tmpCol, val: value});
        });
        
        savedState.filter[0] = {
          cols: tmpFilterCols
        }
      }
      else {
        //model filter
        savedState.filter[0] = {
          cols: [{col: urlVars.item, val: parseInt(urlVars.value)}]
        }
      }
    }
    
    savedState.filter[1] = urlVars.search == "" ? undefined : getSearchFilter(urlVars.search);
    $('#searchBox').val(urlVars.search);
    
    savedState.search = urlVars.search;
    
    savedState.page = parseInt(urlVars.page) > 0 ? parseInt(urlVars.page) : 1;
    
    var NodeMgr = new NodeManager();
    
    var options = {
      modals: false,
      rowClickHandler: function(e) {
        var itemLink = $(e.currentTarget).find('a#itemSelector');
        if(itemLink.length > 0) {
          if(e.target.nodeName != 'INPUT' && e.target.nodeName != 'LABEL' && e.target.parentNode.nodeName != 'LABEL' && e.target.nodeName != 'BUTTON' && e.target.nodeName != 'A' && e.target.parentNode.nodeName != 'A') {
            if(!$(e.target).hasClass('checkone')) {
              Util.goTo($(itemLink.first()[0]).attr('href'));
            }
          }
        }
        return false;
      },
      fieldNames: [{"asset":"hidAssetIdCO","location":"selLocationCO","purpose":"txtPurposeCO","finish":"txtFinishCO","notes":"txtaNotesCO"}, {"asset":"hidAssetIdCI","notes":"txtaNotesCI"}],
      itemName: 'asset',
      filter: savedState.filter,
      order: [{colClass: 'AssetModel', col: 'desc'}, {colClass: 'Custom', col: 'unique_id'}],
      buildTable: function(retVal) {
        
        var checkallBtn = '';
        
        if($('html').attr('data-role') > ISLE_VIEWER) {
          checkallBtn = '<th class="checkall center"><input type="checkbox" name="chkSelectAll" id="chkSelectAll" class="checkall" title="select all" aria-label="select all" value="" /></th>';
        }
        
        $('#nodeTable').html('<thead><th></th><th></th><th></th><th></th>' + checkallBtn + '</thead><tbody></tbody>');

        var nameText = '';
        var imgNo = 0;
        $.each(retVal['items'], function(index, val){
          
          imgNo++;
          $('#nodeTable tbody').append($('<tr><td class="pad0 height3 width5" id="thumbCol' + imgNo + '"></td></tr>'));
          
          if(val['AssetModel_img'] !== null){
            
            $('#thumbCol' + imgNo).append($('<a id="thumbColLink' + imgNo + '" href="' + Util.rootdir + 'uploads/images/assetmodels/' + val['model'] + '.' + val['AssetModel_img'] + '?ts=' + (new Date(val['AssetModel_img_modified']).getTime() / 1000) + '"></a>'));
            
            var imageLoader = new Image();
            imageLoader.setAttribute('data-id', imgNo);
            imageLoader.setAttribute('alt', Util.htmlEncode(val['AssetModel_desc']));
            imageLoader.onload = function(){
              $(this).addClass('thumbImg');

              if(this.width / this.height >= (85/54)) {
                $(this).addClass('fillwidth');
              }
              else {
                $(this).addClass('fillheight');
              }
              var imgNo2 = $(this).attr('data-id');
              //add image to thumbColLink
              $('#thumbColLink' + imgNo2).empty().append($(this));
            }
            imageLoader.src = Util.rootdir + 'uploads/images/assetmodels/thumbs/' + val['model'] + '.' + val['AssetModel_img'] + '?ts=' + (new Date(val['AssetModel_img_modified']).getTime() / 1000);//must go after onload for it to work when retrieving image from cache in ie.
          }
          
          $('#nodeTable tbody tr').last().append($('<td><a id="itemSelector" href="' + Util.rootdir + 'assets/' + val['id'] + '">' + Util.htmlEncode(Util.abbreviate(val['AssetModel_desc'], 400)) + '</a></td>'));
          
          var uniqueId = 'S/N: ' + Util.abbreviate(Util.htmlEncode(val['serial']), 100);
          
          $('#nodeTable tbody tr').last().append($('<td>' + uniqueId + '</td>'));
          
          if(val['Transaction_type'] == 1) { //checked out
            if(val['Transaction_User_uid'] == $('html').attr('data-user')) { //checked-out to me
              //show check-in button
              var dueTitleTxt = '';
              if(val['Transaction_finish'] !== null) {
                dueTitleTxt = Util.htmlEncode($.format.date(Util.parseDate(val['Transaction_finish']).toString(), 'M/d/yyyy'));
              }
              
              $('#nodeTable tbody tr').last().append($('<td><button type="button" name="checkIn" data-asset="' + val['id'] + '" value="checkIn" id="cico' + val['id'] + '" class="btn btn-mini btn-warning" title="Due: ' + dueTitleTxt + '"><i class="icon-white icon-upload"></i> Check-in</button></td>'));
            }
            else { // checked-out to someone else
              //show name of person it's checked out too
              
              nameText = '<a href="' + Util.rootdir + 'assets?item=user&value=' + Util.htmlEncode(val['Transaction_User_uid']) + '">' + Util.htmlEncode(val['Transaction_User_name']) + '</a>';
              if(val['Transaction_User_email'] != null) {
                nameText += ' <a href="mailto:' + Util.htmlEncode(val['Transaction_User_email']) + '" aria-label="send email to user"><i class="icon-envelope"></i></a>';
              }
              
              $('#nodeTable tbody tr').last().append($('<td>Out to ' + nameText + '</td>'));
            }
          }
          else if(val['Transaction_type'] == 3) {
            $('#nodeTable tbody tr').last().append($('<td>Restricted</td>'));
          }
          else {
            if($('html').attr('data-role') > ISLE_VIEWER) {
              $('#nodeTable tbody tr').last().append($('<td><button type="button" name="checkOut" data-asset="' + val['id'] + '" value="checkOut" id="cico' + val['id'] + '" class="btn btn-mini"><i class="icon-download"></i> Check-out</button></td>'));
            }
            else {
              $('#nodeTable tbody tr').last().append($('<td></td>'));
            }
          }
          if($('html').attr('data-role') > ISLE_VIEWER) {
            $('#nodeTable tbody tr').last().append($('<td class="center checkone"><input type="checkbox" class="checkone" value="' + val['id'] + '" id="check' + val['id'] + '" aria-label="select asset" /></td>'));
          }
        });
      },
      urlVars: urlVars,
      savedState: savedState,
      selectItems: true
    }

    // Collapse everything:
    $("#VerColMenu ul").hide();
    //don't collapse the selected category as indicated by url.
    //if ul has an id of VerColMenu don't slidetoggle or set expander.
    
    if($selectedCat) {
      $.each($selectedCat.parents('ul'), function(index){
        if($(this).attr('id') !== 'VerColMenu') {
          $(this).show();
          $(this).prev('.item').children('.expander').html('-');
        }
      });
    }
    
    // Expand or collapse:
    $("#VerColMenu span.expander").on('click keypress', function(e){
      if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
        $(this).html() === "+" ? $(this).html("-") : $(this).html("+");
        $(this).closest("li").children('ul').first().slideToggle("fast");
      }
    });

    NodeMgr.initialize(options);

    $('#showAll, #VerColMenu span.selector').on('click keypress', function(e){
      if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
        
        if (Modernizr.history) {
          $('#searchBox').val('');
          $('#showAll, #VerColMenu span.selector').removeClass('bold-italic');
          
          urlVars.page = 1;
          savedState.page = 1;
          
          if(typeof $(this).attr('data-id') != 'undefined') {
            urlVars.item = "category";
            urlVars.value = $(this).attr('data-id');
            urlVars.search = undefined;           
            
            var catURL = "assets" + Util.createQueryString(urlVars);
            //go down through dom and collect the data-id attributes of descendant categories.
            //remove bold-italic class from all other selectors.

            $(this).addClass('bold-italic');
            var family = $(this).closest('li').find('.selector');
            savedState.category = $(this).attr('data-id');
            savedState.filter[0] = getCategoryFilter(family);
            savedState.search = undefined;
            savedState.filter[1] = undefined;            
          }
          else {
            //show all was clicked
            NodeMgr.props.options.urlVars = {};
            NodeMgr.props.options.savedState = {};
            urlVars = NodeMgr.props.options.urlVars;
            savedState = NodeMgr.props.options.savedState;
            var catURL = "assets" + Util.createQueryString(urlVars);
            
            savedState.filter = [];
          }

          savedState.urlVars = urlVars;
          window.history.pushState(savedState, "", catURL);

          NodeMgr.props.options.filter = savedState.filter;
          NodeMgr.getItems();
        }
        else {
          if(typeof $(this).attr('data-id') != 'undefined') {
            window.location = Util.rootdir + "assets?item=category&value=" + $(this).attr('data-id');
          }
          else {
            window.location = Util.rootdir + "assets";
          }
        }
      }
    });

    $( "#" + options.fieldNames[0].finish ).datepicker({
      showOn: "button",
      buttonImage: Util.rootdir + "cdn/images/calendar.gif"
    });
    
    $('#addItemBtn').focus();
    
    $("#showAll").on('click keypress', function(e){
      if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
        e.preventDefault();
      }
    });
    
    $("#searchBtn").on('click keypress', function(e){
      if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
        e.preventDefault();
        search();
      }
    });
    
    $('#searchBox').on('keypress', function(e){
      if(e.type == 'keypress' && e.which == 13) {
        e.preventDefault();
        search();
      }
    });
    
    function getCategoryFilter(family) {
      var familyIds = [];
      $.each(family, function(index){
        familyIds.push($(this).attr('data-id'));
      });

      var cols = [];

      $.each(familyIds, function(index, value){
        cols.push({
          colClass: 'AssetModelCategory', 
          col: 'category',
          val: value
        });
      });
      var ftr = {
        cols: cols,
        separator: 'OR'
      };
      return ftr;
    }
    
    function search() {
      urlVars = NodeMgr.props.options.urlVars;
      savedState = NodeMgr.props.options.savedState;
      
      urlVars.page = 1;
      savedState.page = 1;
      
      if (Modernizr.history) {

        if($('#searchBox').val() != '') {
          urlVars.search = $('#searchBox').val();
          var catURL = "assets" + Util.createQueryString(urlVars);
          //go down through dom and collect the data-id attributes of descendant categories.
          //remove bold-italic class from all other selectors.
          savedState.filter[1] = getSearchFilter($('#searchBox').val());
          savedState.search = $('#searchBox').val();

        }
        else {
          //search field was submitted blank.
          urlVars.search = undefined;
          var catURL = "assets" + Util.createQueryString(urlVars);
          savedState.search = undefined;
          savedState.filter[1] = undefined;
        }
        
        savedState.urlVars = urlVars;
        window.history.pushState(savedState, "", catURL);
      }
      else {
        if($('#searchBox').val() != '') {
          urlVars.search = $('#searchBox').val();
        }
        else {
          urlVars.search = undefined;
        }
        window.location = Util.rootdir + "assets" + Util.createQueryString(urlVars);
      }
      
      NodeMgr.props.options.filter[1] = savedState.filter[1];

      NodeMgr.getItems();
    }
    
    function getSearchFilter(search) {
      var searchFilter = {};
      
      searchFilter.cols = [{ 
        col: 'serial',
        val: '%' + search + '%',
        operator: 'LIKE'
      },
      { 
        colClass: 'AssetModel', 
        col: 'model',
        val: '%' + search + '%',
        operator: 'LIKE'
      },
      { 
        colClass: 'AssetModel', 
        col: 'desc',
        val: '%' + search + '%',
        operator: 'LIKE'
      },
      { 
        colClass: 'AssetModel', 
        col: 'series',
        val: '%' + search + '%',
        operator: 'LIKE'
      },
      { 
        colClass: 'Manufacturer', 
        col: 'name',
        val: '%' + search + '%',
        operator: 'LIKE'
      }];
      
      searchFilter.separator = 'OR';
      return searchFilter;
    }
    
    window.onpopstate = function(event) {
      //on page load this function runs after the document load function has finished.
      
      if(event.state) {
        $('#showAll, #VerColMenu span.selector').removeClass('bold-italic');
        if(typeof event.state.category != 'undefined') {
          var $selectedCat = $('#VerColMenu span.selector[data-id="' + event.state.category + '"]');
          $selectedCat.addClass('bold-italic');
          //expand its parents if its a subcategory that's hidden.
          $.each($selectedCat.parents('ul'), function(index){
            if($(this).attr('id') !== 'VerColMenu') {
              $(this).show();
              $(this).prev('.item').children('.expander').html('-');
            }
          });
        }
        
        if(typeof event.state.search != 'undefined') {
          $('#searchBox').val(event.state.search);
        }
        else {
          $('#searchBox').val('');
        }
        
        //update urlVars and savedState.
        NodeMgr.props.options.urlVars = event.state.urlVars;
        NodeMgr.props.options.savedState = event.state;
        NodeMgr.props.options.filter = event.state.filter;
        NodeMgr.getItems();
      }
      else {
      //event.state will not be set when loading a page, or sometimes when going back or forward to assets with no url params. event.state will also not be set when you go back to a page you loaded manually using url params
        if(typeof NodeMgr.props.retVal != 'undefined') {
          document.location.reload();
        }
      }
    }
    
    // Add location from check-out dialog.
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
          $('#' + options.fieldNames[0]['location'] + ' option').first().after('<option value="' + addedItem['id'] + '">' + Util.htmlEncode(addedItem['center']) + ' ' + Util.htmlEncode(addedItem['bldg']) + '-' + Util.htmlEncode(addedItem['room']) + '</option');
          $('#' + options.fieldNames[0]['location']).val(addedItem['id']);
          $('#' + options.fieldNames[0]['location']).focus();
        }
      }

      NodeMgrLocation.initialize(optionsLocation);

      $('#' + optionsLocation.dialogId).on('shown', function() {
        //increase the z-index of the modal and its backdrop +20
        $(this).css("z-index", $(this).css("z-index") + 20);
        $('.modal-backdrop').last().css("z-index", $('.modal-backdrop').css("z-index") + 20);
        $('.modal-backdrop').first().hide();
      });

      $('#' + optionsLocation.dialogId).on('hidden', function() {
        $('.modal-backdrop').first().show();
      });

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
    
  }); //end document.ready function
});
