class RecentNotificationsUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			let print = false;
			switch(response.status){
				case SUCCESS:
					for(key in response.getDataStructures()){
						let data = response.getDataStructure(key);
						let type = data.getDataType();
						switch(type){
							case DATATYPE_NOTIFICATION:
								if(print){
									window.alert(f.concat(": updating notification element"));
								}
								data.updateElement();
							default:
								continue;
						}
					}
					resetSessionTimeoutAnimation(true);
					ShortPollForm.scheduleUpdateCheck();
				default:
					console.log(response);
			}
		}catch(x){
			return error(f, x);
		}
	}
}