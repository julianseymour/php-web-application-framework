class Menu extends Basic{
	
	static loadHyperlink(event, element){
		let f = "Menu.loadHyperlink";
		try{
			loadHyperlink(event, element);
			let vpw = getViewportWidth();
			//if(vpw <= 480){
				Menu.close();
			/*}else{
				window.alert("Viewport width is \"".concat(vpw).concat("\""));
			}*/
		}catch(x){
			return error(f, x);
		}
	}
	
	static close(){//toggles the menu open/closed
		let f = "Menu.close()";
		try{
			console.log(f+": entered");
			let m = document.getElementById("menu_toggle");
			if(m === null) {
				console.error(f + " error: unable to find menu toggle checkbox");
				return;
			}
			if(!m.checked) {
				console.log(f+": menu is already closed");
			}else{
				m.checked = !m.checked;
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}

	/*static setMenuHeight(){ //set the height attribute of the menu element
		let f = "setMenuHeight()";
		//console.log(f);
		try{
			let m = document.getElementById("menu_toggle");
			if(m.checked === true) {
				//console.log(f+": menu is toggled open, now closing");
				//m.checked = false;
			}else if(m.checked === false){
				//console.log(f+": menu is toggled closed, now opening");
				let e = document.getElementById("page-1");
				if(e === null) {
					//console.log(f+" error: menu services page element is null");
					return;
				}
				let h = window.innerHeight - 50;
				e.style['height'] = h+"px";
				//console.log(f+": successfully set element height to "+h);
				//m.checked = true;
			}else{
				//console.log(f+" error: menu has paradoxical toggle state \""+m.checked+toString());
			}
			m.checked = !m.checked;
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}*/

	static updateTheme(event, input){
		let f = "Menu.updateTheme()";
		try{
			checkInputWithId(input.value.concat("_theme"));
			return AjaxForm.appendSubmitterName(event, input);
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
	
	//thanks https://stackoverflow.com/a/67988655
	//XXX TODO this should be a generated function
	static setViewportHeightCustomProperty(){
		let f = "setViewportHeightCustomProperty()";
		let print = false;
		/*let vh = window.innerHeight * 0.01;
		if(print){
			console.log("Setting vh property to ".concat(vh));
		}
		document.documentElement.style.setProperty('--vh', `${vh}px`);*/
		let e = null;
		/*e = document.getElementById("menu");
		e.style.top = `${window.innerHeight - e.clientHeight}px`;*/
		if(elementExists("menu_bar")){
			e = document.getElementById("menu_bar");
			e.style.top = '0';
		}
		if(elementExists("messenger_widget_container")){
			e = document.getElementById("messenger_widget_container");
			//e.style.top = `${window.innerHeight - e.clientHeight}px`;
			//e.style.top = `${window.visualViewport.height - e.clientHeight}px`;
		}
		//document.getElementById("debug_printer").innerHTML = "".concat(window.visualViewport.height).concat(" - ").concat(e.clientHeight).concat(" top:").concat(e.style.top);
		if(elementExists("notifications_widget_container")){
			e = document.getElementById("notifications_widget_container");
			//e.style.top = `${window.innerHeight - e.clientHeight}px`;
			e.style.top = `${window.visualViewport.height - e.clientHeight}px`;
		}
		if(elementExists("credit_menu_container")){
			e = document.getElementById("credit_menu_container");
			//e.style.top = `${window.innerHeight - e.clientHeight}px`;
			e.style.top = `${window.visualViewport.height - e.clientHeight}px`;
		}
		//document.getElementById("menu_bar").style['background-color'] = "#".concat(genRanHex(6));
	}
}
