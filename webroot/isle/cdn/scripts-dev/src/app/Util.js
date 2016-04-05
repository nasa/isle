define(['jquery', 'bootstrap-dropdown', 'bootstrap-modal', 'bootstrap-alert', 'jquery.trap.min', 'jquery.dateFormat-1.0', 'jquery-ui-1.8.23.custom.min'], function($) {
  var Util = {};
  
  //for root relative urls in html.
  Util.rootdir = SERVER_ROOTDIR;
  
  Util.currentPage = function() {
    var sPath = window.location.pathname;
    var sPage = sPath.substring(sPath.lastIndexOf(this.rootdir) + this.rootdir.length);
    return sPage;
  }

  Util.htmlEncode = function(value) {
    
    if(typeof value === 'undefined' || !value) {
      return '';
    }
    
    var entityMap = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': '&quot;',
      "'": '&#39;',
      "/": '&#x2F;'
    };
    
    return String(value).replace(/[&<>"'\/]/g, function (s) {
      return entityMap[s];
    });
  }

  Util.htmlDecode = function(value) {
    if(typeof value === 'undefined') {
      return '';
    }
    return $('<div/>').html(value).text();
  }

  Util.capitalize = function(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  Util.abbreviate = function(str, len) {
    if(str.length > len)
      return str.substring(0, len) + '...';
    else
      return str;
  }

  Util.getObjects = function(obj, key, val) {
    var objects = [];
    for (var i in obj) {
        if (!obj.hasOwnProperty(i)) continue;
        if (typeof obj[i] == 'object') {
            objects = objects.concat(Util.getObjects(obj[i], key, val));
        } else if (i == key && obj[key] == val) {
            objects.push(obj);
        }
    }
    return objects;
  }
  
  // parse a date in yyyy-mm-dd format
  Util.parseDate = function(input) {
    var parts = input.match(/(\d+)/g);
    // new Date(year, month [, date [, hours[, minutes[, seconds[, ms]]]]])
    return new Date(parts[0], parts[1]-1, parts[2]); // months are 0-based
  }
  
  Util.parseJSON = function(prefixedJSON, dataType) {
    //if prefixedJSON is not valid json is null, undefined, or empty then throw an error that will make the ajax error function get called.
    var json = prefixedJSON.replace(/^while\(1\);/,"");
    var jsonParsed = $.parseJSON(json);//throws an error if it is not valid json.
    if(typeof json === 'undefined' || json == null || json == "") {
      throw "returned json object was null, undefined, or an empty string.";
    }
    //if the result returned was "server error", throw an exception.
    if(jsonParsed.result.status == "error" && jsonParsed.result.value == "server error") {
      throw "A server error occurred.";
    }
    return json;
  }
  
  Util.getDescendants = function recurse(id, descendants, parents) {
    if($.isArray(parents[id])) {
      $.each(parents[id], function(index, value){
        descendants.push(recurse(value, descendants, parents));
      });
    }
    return id;
  }
  
  Util.getLength = function(input) {
    if($.isArray(input)) {
      return input.length;
    }
    else if($.isPlainObject(input)) {
      var count = 0, i;

      for (i in input) {
        if (input.hasOwnProperty(i)) {
          count++;
        }
      }
      return count;
    }
  }
  
  Util.getParameterByName = function(name)
  {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(window.location.search);
    if(results == null)
      return "";
    else
      return decodeURIComponent(results[1].replace(/\+/g, " "));
  }
  
  Util.getUrlParams = function() {
    var urlParams = {};
    var match,
        pl     = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
        query  = window.location.search.substring(1);

    while (match = search.exec(query))
       urlParams[decode(match[1])] = decode(match[2]);
     
    return urlParams;
  }
  
  Util.createQueryString = function(urlVars) {
    if($.isEmptyObject(urlVars)) {
      return '';
    }
    else {
      var queryString = '?';
      $.each(urlVars, function(key, val){
        if(typeof val != 'undefined' && val != '') {
          queryString += key + "=" + encodeURIComponent(val) + "&";
        }
      });
      return queryString.substring(0, queryString.length - 1);
    }
  }
  
  Util.goTo = function(url) {
    
    var a = document.createElement("a");
    if (a.click)
    {
        // HTML5 browsers and IE support click() on <a>, early FF does not.
        a.setAttribute("href", url);
        a.style.display = "none";
        document.body.appendChild(a);
        a.click();
    } else {
        // Early FF can, however, use this usual method
        // where IE cannot with secure links.
        window.location = url;
    }
  }
  
  /*
   * Take all selects, input type text, textarea, input type files and
   * replace them with:
   * input text: value
   * select: selected option's html.
   * textarea: html
   * file: nothing.
   */
  Util.fieldsToText = function() {
    $('input[type="text"]').replaceWith(function(){
      return '<div class="readOnlyField">' + $(this).val() + '</div>';
    });
    $('select').replaceWith(function(){
      return '<div class="readOnlyField">' + $(this).children('option').filter(':selected').html() + '</div>';
    });
    $('textarea').replaceWith(function(){
      return '<div class="readOnlyField">' + $(this).html() + '</div>';
    });
    $('input[type="file"]').remove();
  }
  
  return Util;
});