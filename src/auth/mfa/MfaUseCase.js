class MfaUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse";
		try{
			console.trace(); //log(response);
			switch(response.status){
				case SUCCESS:
					console.log(f+": MFA login successful");
					InfoBoxElement.resetInfoBox();
					updatePageAfterLogin(response);
					return;
				case ERROR_INVALID_MFA_OTP:
					console.error(f+": MFA login failed");
					InfoBoxElement.showInfoBox(response.info);
					return super.handleResponse(response);
				case RESULT_BFP_RETRY_LOGIN:
				default:
					console.error(f+": default case: \""+response.info+"\"");
					InfoBoxElement.resetInfoBox();
					updateLoginNotice(response.form);
					return super.handleResponse(response);
			}
		}catch(x){
			error(f, x);
		}
	}
}