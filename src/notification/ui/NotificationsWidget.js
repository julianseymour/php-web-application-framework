class NotificationsWidget extends WidgetIcon{
	
	static initializeNotificationsWidget(){
		let f = "initializeNotificationsWidget()";
		try{
			let action = "/notifications/";
			let params = {}; //js:1};
			let id = "notifications_widget_icon";
			let icon = document.getElementById(id);
			let read_multiple = icon.getAttribute("read_multiple");
			if(isset(read_multiple)){
				params['directive'] = 'read_multiple';
				icon.removeAttribute("read_multiple");
			}
			fetch_xhr(
				"POST", action, params, controller, error_cb, getContentTypeString()
			);
			initializeBackgroundSync();
			icon.onclick = null;
		}catch(x){
			return error(f, x);
		}
	}
	
	static insertNotificationElement(note_data){
		let f = "notificationsWidget.insertNotificationElement()";
		try{
			let note_class = note_data.constructor.name;
			if(print){
				console.log(f.concat(": notification data has class \"").concat(note_class).concat("\""));
			}
			let element = NotificationData.bindNotificationElement(note_data);
			if(note_data.hasPinnedTimestamp()){
				insertAfterElement(element, document.getElementById("pin_notification_here"));
			}else{
				insertAfterElement(element, document.getElementById("insert_notification_here"));
			}
			note_data.incrementNotificationTabCounter();
		}catch(x){
			return error(f, x);
		}
	}
	
	static setLoaded(count=null){
		let f = "NotificationsWidget.setLoaded()";
		try{
			document.getElementById("notification_list").setAttribute("loaded", 1);
			if(isset(count)){
				let icon = document.getElementById("notification_count_icon");
				icon.innerHTML = count;
				icon.style['opacity'] = 1;
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	static isLoaded(){
		let f = "NotificationsWidget.isLoaded()";
		try{
			if(!elementExists("notification_list")){
				return false;
			}
			return document.getElementById("notification_list").hasAttribute("loaded");
		}catch(x){
			return error(f, x);
		}
	}
}
