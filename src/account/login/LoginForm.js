function loginButtonClicked(event, button){//XXX here's your fucking problem
	let f = "loginButtonClicked()";
	try{
		event.preventDefault();
		/*if(!validateLoginParameters()){
			console.error(f+": login parameters invalid");
			return;
		}//else{
			console.log(f+": login parameters valid - submitting form");*/
			AjaxForm.appendSubmitterName(event, button);
			let form = document.getElementById("login_form");
			if(!form.onsubmit){
				return error(f, "Form lacks a submit event listener");
			}else{
				console.log(form.onsubmit);
				console.log(f+": about to dispatch a submit event on the login form");
			}
			//form.dispatchEvent(new Event("submit")); //XXX firefox bypasses the onsubmit event for some reason
			let load = "load_login_form";
			AjaxForm.submitForm(
				form, 
				function(response){
					AjaxForm.terminateLoadAnimation(load);
					controller(
						response
						/*, 
						function(response){
							if(response.hasCommands()){
								//console.log(response);
								console.log(f+": about to process media commands");
								console.trace();
								response.processCommands(); //, callback_1, callback_2);
							}else{
								return error(f, "No media commands to process after login");
							}
						}, 
						error_cb*/
					);
				}, 
				function(response){
					AjaxForm.terminateLoadAnimation(load);
					error_cb(form);
				}
			);
		//}
	}catch(x){
		return error(f, x);
	}
}

function isAnonymousUser(){//returns true if the user was not logged in when the page was generated
	let f = "isAnonymousUer()";
	try{
		let o = document.getElementById("not_logged_in");
		if(o == null){
			return false;
		}
		return true;
	}catch (x){
		error(f, x);
	}
}

function getLoginNoticeElement(){
	let f = "getLoginNoticeElement()";
	try{
		return document.getElementById("login_notice");
	}catch(x){
		error(f, x);
	}
}

function updateLoginNotice(notice){
	let f = "updateLoginNotice()";
	try{
		console.log(f+": entered");
		if(getLoginNoticeElement().value == "Please wait..."){
			console.log(f+": login notice element is already being updated; assign an event listener here");
			return;
		}
		console.log(f+": login notice element is not in please wait mode");
		replaceInnerHTMLById("login_notice", notice);
	}catch(x){
		error(f, x);
	}
}

function loginUsernameInputHandler(event, input){
	let login_button = document.getElementById("login_button");
	let password = document.getElementById("login_password_field");
	if(input.value.length < 1 || password.value.length < 12){
		login_button.disabled = true;
	}else{
		login_button.disabled = false;
	}
}

function loginPasswordInputHandler(event, input){
	let login_button = document.getElementById("login_button");
	let username = document.getElementById("login_username_field");
	if(input.value.length < 12 || username.value.length < 1){
		login_button.disabled = true;
	}else{
		login_button.disabled = false;
	}
}

function updatePageAfterLogin(response){
	let f = "updatePageAfterLogin()";
	try{
		let print = false;
		if(print){
			window.alert(f.concat(": entered"));
		}
		if(response == null){
			console.error(f+": response is null");
			console.trace();
			return false;
		}else if (print){
			console.log(f.concat(": about to call initializeBackgroundSync"));
		}
		initializeBackgroundSync();
		if(response.hasCommands()){
			response.processCommands();
		}else if(print){
			console.log(f+": response has no commands");
		}
	}catch(x){
		error(f, x);
	}
}

