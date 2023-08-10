class DismissAllNotificationsUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			switch(response.status){
				case SUCCESS:
					console.log(f+": dismissed all notifications successfully");
					let dismiss_us = document.getElementById("notification_list").getElementsByClassName("dismissable");
					while(dismiss_us.length > 0){
						let dismiss_me = dismiss_us[0];
						dismiss_me.parentNode.removeChild(dismiss_me);
					}
					return super.handleResponse(response);
				default:
					console.error(f+": default case");
					InfoBoxElement.showInfoBox(response.info);
					return;
			}
		}catch(x){
			console.trace();
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
}