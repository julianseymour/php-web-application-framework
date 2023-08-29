class LogoutUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			UseCase.handleResponse(response);
			switch(response.status){
				case RESULT_LOGGED_OUT:
					sessionStorage.clear();
					let containers = document.querySelectorAll(".widget_container");
					for(let i = 0; i < containers.length; i++){
						let container = containers[i];
						container.style['opacity'] = '1';
						container.style['pointer-events'] = 'auto';
					}
					break;
				default:
					break;
			}
		}catch(x){
			error(f, x);
		}
	}
}
