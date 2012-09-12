if (typeof(SLogin) === 'undefined') {
	var SLogin = {};
}


SLogin.intit = function() {
	var block = document.getElementById('slogin-buttons');
	if (block === null) return;
	var elements = block.getElementsByTagName('a');
	var params = "resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes"
	var regexp = /width=(.*[\d]),height=(.*[\d])/
	for (var i = 0; i < elements.length; i++) {
		elements[i].onclick = function(e){
			if (typeof(PopUpWindow) == 'window') {
				PopUpWindow.close();
			}
			var el = this.getElementsByTagName('span');
			var size = SLogin.getPopUpSize(el[0].className);
			var result = regexp.exec(size)
			var popup_width = result[1];
			var popup_height = result[2];
			var win_size = SLogin.WindowSize();
			var centerWidth = (win_size[1] - popup_width) / 2;
			var centerHeight = (win_size[2] - popup_height) / 2;
			var PopUpWindow = window.open(this.href, 'LoginPopUp', size + ',left=' + centerWidth + ',top=' + centerHeight + ',' + params);
			PopUpWindow.focus();
			return false;
		}
		
	}	
	
}
SLogin.WindowSize = function() {
	var myWidth = 0, myHeight = 0, size = [];
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	size[1] = myWidth;
	size[2] = myHeight;
	
	return size;
}

SLogin.getPopUpSize = function(el)
{
	var size = null; 
	switch (el) {
		case 'vkontakte':
			size = 'width=585,height=350';
			break;
		case 'google':
			size = 'width=650,height=450';
			break;
		case 'facebook':
			size = 'width=900,height=550';
			break;
		case 'twitter':
			size = 'width=900,height=550';
			break;
		default:
			size = 'width=450,height=380';
			break;
	}
		
	return size;
}

window.onload = SLogin.intit;