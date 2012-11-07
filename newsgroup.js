

/* cookie functions from quirksmode.org, with tiny modifications */
function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";
  document.cookie = 'ng-' + name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = 'ng-' + name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}


function goto_post(dir)
{
    var h = window.location.hash;
    var s = window.location.search;
    var postnums = new Array();
    if (s.match(/^\?[0-9,]+/)) {
	postnums = s.substring(1).split(",");
    }
    if (postnums.length < 2) return;
    if (h.match(/^#p[0-9]+/)) {
	h = h.substring(2);
	for (var i = 0; i < postnums.length; i++) {
	    if (postnums[i] == h) {
		var nxt;
		i = i + dir;
		if (i < 0) i = postnums.length - 1;
		else i = (i % postnums.length);
		nxt = postnums[i];
		window.location.hash = '#p'+nxt;
		return;
	    }
	}
    } else {
	window.location.hash = '#p'+postnums[0];
    }
}

function goto_index()
{
    window.location = window.location.href.slice(0, -(window.location.search.length + window.location.hash.length));
}

var enable_navigation = readCookie('usejsnav');
var key_goto_next_post = readCookie('jsnav-goto-next');
var key_goto_prev_post = readCookie('jsnav-goto-prev');
var key_goto_index = readCookie('jsnav-goto-index');

function handle_keyb(e)
{
  if( !e ) {
    //if the browser did not pass the event information to the
    //function, we will have to obtain it from the event register
    if( window.event ) {
      //Internet Explorer
      e = window.event;
    } else {
      //total failure, we have no way of referencing the event
      return;
    }
  }

  var shift_key = e.shiftKey;
  var ctrl_key = e.ctrlKey;
  var alt_key = e.altKey;

  if( typeof( e.keyCode ) == 'number'  ) {
    //DOM
    e = e.keyCode;
  } else if( typeof( e.which ) == 'number' ) {
    //NS 4 compatible
    e = e.which;
  } else if( typeof( e.charCode ) == 'number'  ) {
    //also NS 6+, Mozilla 0.9+
    e = e.charCode;
  } else {
    //total failure, we have no way of obtaining the key code
    return;
  }

  var str = String.fromCharCode(e);
  if (shift_key)
    str = str.toUpperCase();
  else
    str = str.toLowerCase();

    if (pagetype == 'showpost') {
	if (key_goto_next_post != null && (str == key_goto_next_post)) {
	    goto_post(1);
	} else if (key_goto_prev_post != null && (str == key_goto_prev_post)) {
	    goto_post(-1);
	} else if (key_goto_index != null && (str == key_goto_index)) {
	    goto_index();
	}
    } else if (pagetype == 'search') {
    } else {
	/* index */
    }

}

if (enable_navigation) {
    document.onkeyup = handle_keyb;
}