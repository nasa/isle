requirejs.config(configOpts);

require(['jquery', 'bootstrap-dropdown', 'bootstrap-modal', 'bootstrap-alert', 'jquery.trap.min', 'jquery.dateFormat-1.0', 'jquery-ui-1.8.23.custom.min', 'jquery.combobox', 'tag-it', 'modernizr', 'app/Util', 'app/NodeManager'], function ($, bdd, bm, ba, jt, jdf, jui, jcb, ti, mod, Util, NodeManager) {
  
  $('.mobile-navicon').click(function(e){
    $('div#nav').slideToggle("fast");
  });
  
  //logout button handler.
  var csrfTok = $('#csrfToken').html();
  
  $('#logout').on('click keypress', function(e){
    if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
      e.preventDefault();
      // destroy session.
      $.ajax({
        //show a loading animation.
        url: Util.rootdir + 'remoteInterface',
        headers: {'x-csrftoken': csrfTok},
        type: 'POST',
        data: {method: 'logout'},
        dataType: 'json',
        dataFilter: Util.parseJSON,
        error: function(jqXHR, textStatus, errorThrown) {
          alert("I'm sorry. Something went wrong (" + errorThrown + ").");
          return false;
        },
        success: function(data, textStatus, jqXHR) {
          //invalidate basic auth credentials.
          $.ajax({
            //show a loading animation.
            url: Util.rootdir + 'remoteInterface',
            headers: {'x-csrftoken': csrfTok},
            type: 'POST',
            username: 'LoggedOut',
            password: 'LoggedOut',
            data: {method: 'logout'},
            dataType: 'json',
            dataFilter: Util.parseJSON,
            error: function(jqXHR, textStatus, errorThrown) {
              //thrown when pressing cancel on the auth dialog.
              //todo: redirect to a non basic authed page with text saying they have been logged out.
              window.location = Util.rootdir + 'assets';
            },
            success: function(data, textStatus, jqXHR) {
              //valid credentials passed to auth dialog.
              window.location = Util.rootdir + 'assets';
            }
          });
        }
      });
    }
  });
  
  // Leave feedback handler
  
  var NodeMgrFeedback = new NodeManager();
  var feedbackFieldNames = {"type":"selType","description":"txtaDescription","steps":"txtaSteps","attachment":"filBugAttachment"};
  var $whichLink = $('.feedbackLink').last();
  
  $('#container').on("click keypress", ".feedbackLink", function(e){
    if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
      e.preventDefault();
      $whichLink = $(e.currentTarget);
      $("#feedbackDialog.modal").trap();

      //reset the dialog
      
      if(window.FormData === undefined) {
        //show a msg with a link to caniuse for supported browsers instead of file input.
        $('#'+feedbackFieldNames['attachment']).replaceWith("<span>Your browser doesn't support attachments. " + '<a href="http://caniuse.com/#feat=xhr2" target="_blank">Here are some that do.</a></span>');
      }
      
      $('#feedbackMsg').remove();
      $("#feedbackDialog .modal-body .formItem, #feedbackDialog .modal-body p.marginB10").show();
      $('#feedbackBtn').show();
      $('#feedbackDialog.modal button[name="cancel"]').html('Cancel');
      $("#feedbackDialog .modal-footer .footerRight").remove();
      $('#feedbackDialog input[type="text"], #feedbackDialog textarea').val('');
      var control = $('#feedbackDialog input[type="file"]').first();
      control.replaceWith( control.val('').clone( true ) );
      $('#feedbackDialog select').val($('#feedbackDialog select option:first').val());
      $('#feedbackDialog input.ui-widget').val('');
      $('#feedbackDialog input[type="checkbox"]').attr('checked', false);

      //change desc label
      $('#feedbackDialog label[for="' + feedbackFieldNames['description'] + '"]').html('What happened?');
      //show the steps field.
      $('#feedbackDialog #' + feedbackFieldNames['steps']).parents('.formItem').filter(':first').show();

      NodeMgrFeedback.removeErrors();

      // show the dialog.
      $("#feedbackDialog").modal('show');
    }
  });//end feedbackLink click handler
  
  $('#feedbackDialog').on('shown', function(){
    $('#feedbackDialog select:visible:first').focus().select();
  });
  
  $('#feedbackDialog #' + feedbackFieldNames['type']).change(function(e){
    NodeMgrFeedback.removeErrors();
    switch($(e.currentTarget).val()) {
      case 'bug':
        //change desc label and erase textarea contents.
        $('#feedbackDialog label[for="' + feedbackFieldNames['description'] + '"]').html('What happened?');
        $('#feedbackDialog #' + feedbackFieldNames['description']).val('');
        //show the steps field.
        $('#feedbackDialog #' + feedbackFieldNames['steps']).parents('.formItem').filter(':first').show();
        break;
      case 'feature':
      case 'chore':
        //change desc label and erase textarea contents.
        $('#feedbackDialog label[for="' + feedbackFieldNames['description'] + '"]').html('Description');
        $('#feedbackDialog #' + feedbackFieldNames['description']).val('');
        //hide the steps field.
        $('#feedbackDialog #' + feedbackFieldNames['steps']).parents('.formItem').filter(':first').hide();
        break;
    }
  });
  
  //handle the cancel and close events
  $('#feedbackDialog.modal button.close, ' + '#feedbackDialog.modal button[name="cancel"]').on('click keypress', function(e){
    if(e.type == 'click' || (e.type == 'keypress' && e.which == 13)) {
      $whichLink.focus();
      e.preventDefault();//ie wasn't closing the modal on enter keypress. this fixed it.
      $('#feedbackDialog').modal('hide');
    }
  });

  // handle modal add, save, and delete click events
  $('#feedbackForm .modal-footer button[name="submit"]').click(function(e){
    e.preventDefault();//Prevents the form from submitting which produces a Firefox error. http://stackoverflow.com/questions/5545577/ajax-post-handler-causing-uncaught-exception

    //disable the buttons. show loading indicator.
    $("#feedbackDialog .modal-footer button").attr("disabled", "disabled");
    $("#feedbackDialog .modal-footer .footerRight").remove();
    $("#feedbackDialog .modal-footer").prepend('<span class="footerRight">Please wait <img class="vertical-align-children-middle" src="' + Util.rootdir + 'cdn/images/loading.gif" alt="loading animation"/></span>');

    // grab all input text, radio, checkbox, textarea, select
    var o = {};
    var a = $('#feedbackForm').serializeArray();

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
    
    var formData = {method: 'feedback', args: [o, feedbackFieldNames]};
    var ctype = 'application/x-www-form-urlencoded; charset=UTF-8';
    var pdata = true;
    
    if(window.FormData !== undefined) {
      ctype = false;
      pdata = false;
      formData = new FormData();
      formData.append("method", "feedback");
      $.each(o, function(key, value){
        formData.append("args[0]["+key+"]", value);
      });
      
      $.each(feedbackFieldNames, function(key, value){
        formData.append("args[1]["+key+"]", value);
      });
      
      formData.append(feedbackFieldNames['attachment'], $('#'+feedbackFieldNames['attachment'])[0].files[0]);
    }
    
    $.ajax({
      url: Util.rootdir + 'remoteInterface',
      headers: {'x-csrftoken': csrfTok},
      type: 'POST',
      data: formData,
      contentType: ctype,
      processData: pdata,
      dataType: 'json',
      dataFilter: Util.parseJSON,
      error: function(jqXHR, textStatus, errorThrown) {
        // enable the buttons
        $("#feedbackDialog .modal-footer button").removeAttr("disabled");
        //replace loading text with error text.
        $("#feedbackDialog .modal-footer .footerRight").addClass('errorText')
            .html('I\'m sorry. Something went wrong (' + errorThrown + ').');
        var retVal2 = '';
        return false;
      },
      success: function(data, textStatus, jqXHR) {
        var retVal2 = data['result']['value'];

        $("#feedbackDialog .modal-footer .footerRight").remove();
        NodeMgrFeedback.removeErrors();

        var focusSet = false;

        //no errors so submit the form.
        if(data['result']['status'] === 'success') {
          $("#feedbackDialog .modal-footer button").removeAttr("disabled");
          $("#feedbackDialog .modal-body .formItem, #feedbackDialog .modal-body p.marginB10").hide();
          $('#feedbackBtn').hide();
          $('#feedbackDialog.modal button[name="cancel"]').html('Close');
          
          $('#feedbackDialog .modal-body').prepend('<div id="feedbackMsg" role="alert" aria-label="Your feedback has been successfully submitted." class="fade in alert alert-success">Your feedback has been successfully submitted.</div>');
        }
        else {// there were errors
          var fi, formField, msgElement;
          // UI Exception

          //loop over retVal structure. find element with name that matches retVal.item name. Find the first parent with formItem class and add class inError. Inside the formItem class find the element with err class and set inner html to retVal.item value.
          $.each(retVal2, function(key, val) {
            if(!focusSet) {
              NodeMgrFeedback.addError('#feedbackDialog', feedbackFieldNames[key], val, true);
              focusSet = true;
            }
            else {
              NodeMgrFeedback.addError('#feedbackDialog', feedbackFieldNames[key], val, false);
            }
          });
          // enable the buttons
          $("#feedbackDialog .modal-footer button").removeAttr("disabled");
        } //end if there are no errors.
      }//end success
    });//end ajax
  });//end add, save, delete button click event handler
  
  if($('html').attr('data-page') !== 'oops') { //don't load page js if on the oops page.
    var view;
    var sPage = Util.currentPage();
    var sWholePage = sPage;

    if(sPage.indexOf('/') > 0) {
      sPage = sPage.substring(0, sPage.indexOf('/') + 1);
    }

    switch (sPage) {
      case 'manufacturers' :
        view = ['app/views/manufacturers'];
        require(view, function () { });
        break;
      case 'assetmodels/' :
        view = ['app/views/assetmodels'];
        require(view, function () { });
        break;
      case 'locations' :
        view = ['app/views/locations'];
        require(view, function () { });
        break;
      case 'assets':
        view = ['app/views/assets'];
        require(view, function () { });
        break;
      case 'assets/':
        view = ['app/views/assetForm'];
        require(view, function () { });
        break;
      case 'categories':
        view = ['app/views/categories'];
        require(view, function () { });
        break;
      case 'attributes':
        view = ['app/views/attributes'];
        require(view, function () { });
        break;
      case 'attributetypes':
        view = ['app/views/attributetypes'];
        require(view, function () { });
        break;
      case 'relations' :
        view = ['app/views/relations'];
        require(view, function () { });
        break;
      case 'users' :
        view = ['app/views/users'];
        require(view, function () { });
        break;
      case 'versionhistory' :
      case 'versionhistory/':
        view = ['app/views/versions'];
        require(view, function () { });
        break;
    }
  }
});
