class LogoutForm extends AjaxForm{
	
	static logoutButtonClicked(event, button){
		let f = "logoutButtonClicked()";
		try{
			//XXX TODO if the user has service workers enabled, unregister them
			disableWidgets();
			let containers = document.querySelectorAll(".widget_container");
			for(let i = 0; i < containers.length; i++){
				let container = containers[i];
				container.style['pointer-events'] = 'none';
				container.style['opacity'] = '0';
			}
			checkInputWithId("widget-none");
			//window.alert("Pause");
			AjaxForm.appendSubmitterName(event, button);
		}catch(x){
			error(f, x);
		}
	}
}