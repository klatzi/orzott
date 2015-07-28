var beerk = null;
var teszt = false;
$.ajaxSetup({ cache: false });
function ajaxCall( func, d,asyn,fn) {
  var res;
  console.debug('ajax:'+func+' data:'+JSON.stringify(d));
  $.ajax({
        type: "POST",
		url: "http://192.168.22.200/orzottsrv/service.php/" + func, /* akh eles */
		//url: "http://192.168.1.68:82/orzottsrv/service.php/" + func, /* otthoni eles */
        data: d,
		async: asyn,
        dataType: "json",
        success: function(data) {
			//console.debug('ajax success:'+fn+' data:'+JSON.stringify(data));
			res=data; 
			if (fn) {
				f=fn.split(".");
				if (f.length>1) {
					var myFunc = window[f[0]][f[1]];
				}
				else {
					var myFunc = window[fn];
				}
				if(typeof myFunc === 'function') {
					//console.debug('ajax success,start func:'+fn+' data:'+JSON.stringify(data));
					myFunc(data);
				}
			  
			}
        },
        error: function(data) {
            console.debug('ajax error:'+func+' data:'+JSON.stringify(data));
			res='ERROR';
        }
  });

  return res;
}




function showMenu() {
	panelName = 'menu';
	$.get( "css/"+panelName+".css", function( data ) {
		css = '<head><style>' + data + '</style></head>';
		$.get( "views/"+panelName+".tpl", function( data ) { 
			tpl = data; 
			$('#divContent').html(css + tpl);

			$('#bbeerk').bind('click',function () {
				beerk = new OBeerk();
			}) 

			$('#divContent').show();

		});
		
	})
}

function showLogin() {
	panelName = 'login';
	$.get( "css/"+panelName+".css", function( data ) {
		css = '<head><style>' + data + '</style></head>';
		$.get( "views/"+panelName+".tpl", function( data ) { 
			tpl = data; 
			$('#divContent').html(css + tpl);
			$('#divContent').show();

		});
		
	})
}

function orzottLogin(){
		user = $('.divinput').text();
		fn='checkLogin'
		ajaxCall(fn,{'user':user},true, fn);
}
function checkLogin(result){
	if (result[0].RCOUNT==0) {
		alert('Nem megfelel� felhaszn�l�i k�d!');
	}
	else {
		login_id=$('.divinput').text();
		login_id = login_id.replace(/(?:\r\n|\r|\n|\t)+/g, '');
		app.getPrinters();
	}
}

$(document).ready(function () {
	showLogin();
})

