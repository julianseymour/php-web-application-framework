class NotificationsUseCase extends UseCase{
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			let print = false;
			let count = 0;
			for(let key in response.getDataStructures()){
				let note = response.getDataStructure(key);
				if(note.getDataType() !== DATATYPE_NOTIFICATION){
					let err = f.concat(": object with key \"").concat(key).concat("\" is not a notification");
					//console.log(err);
					continue;
				}
				let id = "notification-".concat(note.getNotificationIdSuffix());
				if(elementExists(id)){
					if(print){
						console.log("Element with ID \"".concat(id).concat("\" already exists"));
					}
					continue;
				}else if(print){
					console.log("Element with ID \"".concat(id).concat("\" does not already exist"));
				}
				NotificationsWidget.insertNotificationElement(note);
				count++;
			}
			let id = "notifications_widget_icon";
			let icon = document.getElementById(id);
			icon.onclick = ShortPollForm.poll;
			NotificationsWidget.setLoaded(count);
		}catch(x){
			return error(f, x);
		}
	}
}
