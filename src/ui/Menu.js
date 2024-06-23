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
			if(m === null){
				console.error(f + " error: unable to find menu toggle checkbox");
				return;
			}
			if(!m.checked){
				console.log(f+": menu is already closed");
			}else{
				m.checked = !m.checked;
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}

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
	static setViewportHeightCustomProperty(){
		let f = "setViewportHeightCustomProperty()";
		let print = false;
		let widgets = document.querySelectorAll(".widget_container:not(.default-viewport-height)");
		for(let i = 0; i < widgets.length; i++){
			let e = widgets[i];
			if(!isElement(e)){
				console.error(f.concat(": element ").concat(i).concat(" is not an element"));
				console.log(e);
				continue;
			}
			e.style.top = `${window.visualViewport.height - e.clientHeight}px`;
		}
	}
}
