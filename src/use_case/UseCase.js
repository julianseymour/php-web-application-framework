class UseCase{
	
	static handleResponse(response){
		if(isset(response.info)){
			InfoBoxElement.showInfoBox(response.info);
		}
		if(response.hasCommands()){
			response.processCommands();
		}
	}
}