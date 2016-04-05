/* jQuery UI autocomplete Combobox */
;(function($, window, document, undefined) {
  $.widget( "ui.combobox", {
    options: {
      customRenderOptions: false
    },
    _create: function() {
      var input,
      self = this,
      select = this.element.hide(),
      selected = select.children( ":selected" ),
      value = selected.val() ? selected.text() : "",
      wrapper = this.wrapper = $( "<span>" )
      .addClass( "ui-combobox" )
      .insertAfter( select );

      input = $( "<input>" )
      .appendTo( wrapper )
      .val( value )
      .addClass( "ui-state-default ui-combobox-input" )
      .autocomplete({
        delay: 0,
        minLength: 0,
       // source, select, and focus functions modified to increase speed when dealing with large lists. limits list to first 100 results. taken from: http://stackoverflow.com/a/6865343
        source: function( request, response ) {
          var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
          var select_el = select.get(0); // get dom element
          var rep = new Array(); // response array
          var maxRepSize = 100; // maximum response size  
          // simple loop for the options
          for (var i = 0; i < select_el.length; i++) {
            var text = select_el.options[i].text;
            if ( select_el.options[i].value && ( !request.term || matcher.test(text) ) )
              // add element to result array
              rep.push({
                  label: text, // no more bold
                  value: text,
                  option: select_el.options[i]
              });
            if ( rep.length > maxRepSize ) {
              rep.push({
                label: "... more available",
                value: "maxRepSizeReached",
                option: ""
              });
              break;
            }
          }
          // send response
          response( rep );
        },          
        focus: function( event, ui ) {
          if ( ui.item.value == "maxRepSizeReached") {
            return false;
          }
        },
        select: function( event, ui ) {
          
          if ( ui.item.value == "maxRepSizeReached") {
              return false;
          } else {
              ui.item.option.selected = true;
              self._trigger( "selected", event, {
                  item: ui.item.option
              });
          }
        },
        change: function( event, ui ) {
          if ( !ui.item ) {
            var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
            valid = false;
            select.children( "option" ).each(function() {
              if ( $( this ).text().match( matcher ) ) {
                this.selected = valid = true;
                return false;
              }
            });
            if ( !valid ) {
              // remove invalid value, as it didn't match anything
              $( this ).val( "" );
              select.val( "" );
              input.data( "autocomplete" ).term = "";
              return false;
            }
          }
        }
      })
      .addClass( "ui-widget ui-widget-content ui-corner-left" );

      input.data( "autocomplete" )._renderItem = function( ul, item ) {
        
        if(self.options.customRenderOptions) {
          self._trigger( "renderDD", this.event, {
            ul: ul,
            item: item
          });
        }
        else {
          return $( "<li></li>" )
          .data( "item.autocomplete", item )
          .append( "<a>" + item.label + "</a>" )
          .appendTo( ul );
        }
      };

      $( "<a>" )
      .attr( "tabIndex", -1 )
      .attr( "title", "Show All Items" )
      .appendTo( wrapper )
      .button({
        icons: {
          primary: "ui-icon-triangle-1-s"
        },
        text: false
      })
      .removeClass( "ui-corner-all" )
      .addClass( "ui-corner-right ui-combobox-toggle" )
      .click(function() {
        // close if already visible
        if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
          input.autocomplete( "close" );
          return;
        }

        // work around a bug (likely same cause as #5265)
        $( this ).blur();

        // pass empty string as value to search for, displaying all results
        input.autocomplete( "search", "" );
        input.focus();
      });
    },

    destroy: function() {
      this.wrapper.remove();
      this.element.show();
      $.Widget.prototype.destroy.call( this );
    }
  });
})(jQuery, window, document);