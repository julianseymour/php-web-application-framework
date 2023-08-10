class LogoutUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			UseCase.handleResponse(response);
			switch(response.status){
				case RESULT_LOGGED_OUT:
					sessionStorage.clear();
					break;
				default:
					break;
			}
		}catch(x){
			error(f, x);
		}
	}
}
