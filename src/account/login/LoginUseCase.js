class LoginUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			console.log(f+": got login result \""+response.status+"\"");
			switch(response.status){
				case SUCCESS:
					console.log(f+": login successful");
					sessionStorage.clear();
					updatePageAfterLogin(response);
					return;
				case ERROR_LOGIN_CREDENTIALS:
					console.log(f+": login credentials failed");
					updateLoginNotice(response.info);
					break;
				case ERROR_XSRF:
					console.log(f+": session expired");
					if(isset(response.user_cp)){
						console.log(f+": session expired; replacing login form");
						replaceInnerHTMLById("login_replace", response.user_cp, initializeLoginForm, error_cb);
					}
					InfoBoxElement.showInfoBox(response.info);
					//console.log(f+": placeholder: replace login form XSRF tokens with fresh ones");
					break;
				case RESULT_BFP_MFA_CONFIRM:
					console.log(f+": user has MFA enabled; displaying confirmation form");
					response.processCommands();
					break;
				case RESULT_RESET_SUBMIT:
					console.log(f+": successfully submitted password reset request");
					updateLoginNotice(response.info);
					break;
				default:
					let err = f+": default case: \""+response.status+"\"";
					console.error(err);
					//console.log(response);
					if(isset(response.info)){
						console.log(f+": response has an infobox message");
						InfoBoxElement.showInfoBox(response.info);
					}else{
						console.log(f+": response does not have an infobox message");
						InfoBoxElement.showInfoBox(err);
					}
					return; //callback_error();
			}
		}catch(x){
			error(f, x);
		}
	}
}
