class ShortPollUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			switch(response.status){
				case SUCCESS:
					resetSessionTimeoutAnimation(true);
					ShortPollForm.scheduleUpdateCheck();
					break;
				default:
					console.log(response);
					return error(f, "Something went wrong");
			}
		}catch(x){
			return error(f, x);
		}
	}
}