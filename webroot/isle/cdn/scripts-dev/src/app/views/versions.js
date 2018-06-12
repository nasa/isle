define(["jquery", "../Util", "../NodeManager"], function($, Util, NodeManager) {
  //Document.ready function
  $(function() {
    
    var sPageAction = Util.getParameterByName('action');
    var sPage = Util.currentPage();
    var sWholePage = sPage;

    if(sPage.indexOf('/') > 0) {
      sPageAction = sPage.substring(sPage.indexOf('/') + 1, sPage.length);
      sPage = sPage.substring(0, sPage.indexOf('/') + 1);
    }
    
    //get url vars.
    var urlVars = {};
    var savedState = {};
    savedState.filter = [];
    urlVars.action = sPageAction;
    var $selectedCat = false;
    
    //whitelist validate the item. and make sure value is an int.
    var patt = new RegExp(/^[0-9]+\.[0-9]+(\.[0-9]+)?$/);
    if(patt.test(urlVars.action)) {
      //category filter
      savedState.category = urlVars.action;
      savedState.filter[0] = {cols: [{col: 'version', val: urlVars.action}]};
    }
    
    var NodeMgr = new NodeManager();

    var options = {
      fieldNames: {"id":"hidId","version":"txtVersion","revision":"txtRevision","date":"txtDate","description":"txtaDescription"},
      itemName: 'version',
      order: [{col: 'version', dir: 'DESC'}],
      filter: savedState.filter,
      buildTable: function(retVal) {
        var tableHTML = '<tbody>';

        $.each(retVal['items'], function(index, val){
          tableHTML += '<tr><td><h3>Version ' + Util.htmlEncode(val['version']) + '</h3>';
          tableHTML += 'Posted: ' + Util.htmlEncode($.format.date(Util.parseDate(val['date']).toString(), 'MMMM d, yyyy')) + '<br />';
          tableHTML += 'Revision: ' + Util.htmlEncode(val['revision']) + '<br />';
          tableHTML += val['description'];
          tableHTML += '</td></tr>';
        });
        tableHTML += '</tbody>'
        $('#nodeTable').html(tableHTML);
      },
      getCallback: function(retVal, setFocus) {
        //delete all li's from #VerColMenu
        $('#VerColMenu').html('');
        
        $.ajax({
          url: Util.rootdir + 'remoteInterface',
          headers: {'x-csrftoken': $('#csrfToken').html()},
          data: {model: 'Version', method: 'getAll'},
          dataType: 'json',
          dataFilter: Util.parseJSON,
          error: function(jqXHR, textStatus, errorThrown) {
            //an error occurred on the server.
            $('#VerColMenu').html('<div class="errorText">I\'m sorry.<br />Something went wrong (' + errorThrown + ').<br />Feel free to <a class="feedbackLink" href="#">submit a bug report</a>.</div>');
            return false;
          },
          success: function(data, textStatus, jqXHR) {
            var retVal2 = data['result']['value'];
            //add versions to left col.
            $.each(retVal2['items'], function(index, val){
              if(index == 0) {
                $('#VerColMenu').append('<li><a id="showAll" class="fontsize floatRight marginT6" href="' + Util.rootdir + 'versionhistory">show all</a><h4 class="inline-block">Versions</h4></li>');
              }
              //if urlvars.action = val['version'] make it bold-italic.
              var selectedStyle = urlVars.action == val['version'] ? ' bold-italic' : '';
              $('#VerColMenu').append('<li><span class="item"><span class="selector' + selectedStyle + '" tabindex="0" role="button" data-id="' + val['version'] + '">' + Util.htmlEncode(val['version']) + '</span></span></li>');
            });
          }
        });
      }
    }

    NodeMgr.initialize(options);
    
    $('#container').on('click keypress', '#showAll, #VerColMenu span.selector', function(e){
      if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
        
        if (Modernizr.history) {
          $('#showAll, #VerColMenu span.selector').removeClass('bold-italic');
          
          if(typeof $(this).attr('data-id') != 'undefined') {
            urlVars.action = $(this).attr('data-id');

            var catURL = Util.rootdir + "versionhistory/" + encodeURIComponent(urlVars.action);
            //go down through dom and collect the data-id attributes of descendant categories.
            //remove bold-italic class from all other selectors.

            $(this).addClass('bold-italic');
            var family = $(this).closest('li').find('.selector');
            savedState.category = encodeURIComponent(urlVars.action);
            savedState.filter[0] = getCategoryFilter(family);
          }
          else {
            //show all was clicked
            NodeMgr.props.options.urlVars = {};
            NodeMgr.props.options.savedState = {};
            urlVars = NodeMgr.props.options.urlVars;
            savedState = NodeMgr.props.options.savedState;
            var catURL = Util.rootdir + "versionhistory";
            
            savedState.filter = [];
          }

          savedState.urlVars = urlVars;
          window.history.pushState(savedState, "", catURL);

          NodeMgr.props.options.filter = savedState.filter;
          NodeMgr.getItems();
        }
        else {
          if(typeof $(this).attr('data-id') != 'undefined') {
            window.location = Util.rootdir + "versionhistory/" + encodeURIComponent($(this).attr('data-id'));
          }
          else {
            window.location = Util.rootdir + "versionhistory";
          }
        }
      }
    });
    
    $("#container").on('click keypress', '#showAll', function(e){
      if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
        e.preventDefault();
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
          colClass: 'Version',
          col: 'version',
          val: value
        });
      });
      var ftr = {
        cols: cols,
        separator: 'OR'
      };
      return ftr;
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
    
  });
});
