/*function initializePushSettingsElement(){
	let f = "initializePushSettingsElement()";
	try{
		document.getElementById("push_settings_wrapper").style["display"] = "";
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function deselectMenuSettings(){
	let f = "deselectMenuSettings()";
	try{
		let none = document.getElementById("radio_settings_none");
		none.dispatchEvent(new Event('change'));
		none.checked = true;
	}catch(x){
		error(f, x);
	}
}

function refreshUpdatedSettingsForm(json, form_id, reinitialization_callback){
	let f = "refreshUpdatedSettingsForm()";
	try{
		deselectMenuSettings();
		replaceInnerHTMLById(form_id, json.form, reinitialization_callback);
		InfoBoxElement.showInfoBox(json.info);
	}catch(x){
		error(f,x);
	}
}

function initializeUserSettingsForm(){
	let f = "initializeUserSettingsForm()";
	try{
		console.log(f+": entered; about to initialize resend activation email form");
		
		//0. set submit handler for resend activation email
		let resend = document.getElementById("resend_activation_form");
		if(resend == null){
			console.log(f+": user has already activated account; skipping form initialization");
		}else{
			setFormSubmitHandler(resend, callback_resend_activation, error_cb);
			console.log(f+": initialized resend activation email form; about to enable push notification settings");
		}

		//I. enable push notifications settings element (hidden for non-js)
		initializePushSettingsElement();
		console.log(f+": about to set notification settings form submit event handler");
		
		//II. set submit handler for notification settings form
		let nsf = document.getElementById("notification_settings_form");
		setFormSubmitHandler(nsf, callback_notification_settings, error_cb);
		console.log(f+": about to set MFA settings form submit event handler");
		
		//III. set submit handler for authentication settings form
		let masf = document.getElementById("mfa_settings_form");
		setFormSubmitHandler(masf, callback_mfa_settings, error_cb);
		console.log(f+": about to set change password form submit event handler");
		
		//IV. set submit handler for change password form
		let psf = document.getElementById("change_password_form");
		setFormSubmitHandler(psf, callback_change_password, error_cb);
		console.log(f+": about to set change email form submit event handler");
		
		//V. set submit handler for change email form
		let esf = document.getElementById("change_email_form");
		setFormSubmitHandler(esf, callback_change_email, error_cb);
		
		//VI. set submit handler for display name form
		initializeDisplayNameForm();
		
		//VII. theme settings
		let tsf = document.getElementById("theme_settings_form");
		setFormSubmitHandler(tsf, callback_theme_settings, error_cb);
		
		//VIII. Logout
		initializeLogoutForm();
		
		console.log(f+": successfully initialized user settings form");
	}catch(x){
		console.error(f+" exception: "+x.toString());
		return;
	}
}*/

/*function enableAutodetectTimezoneButton(){
	document.getElementById("autodetect_timezone").style['display'] = 'inline-block';
}*/

function autodetectTimezone(){
	let f = "autodetectTimezone()";
	try{
		document.getElementById("timezone_select").value = Intl.DateTimeFormat().resolvedOptions().timeZone;
	}catch(x){
		return x(f, x);
	}
}

