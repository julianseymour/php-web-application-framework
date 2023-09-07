class NotificationData extends UserCorrespondence{
	
	getNotificationType(){
		return this.subtype;
	}
	
	hasPinnedTimestamp(){
		return isset(this.pinnedTimestamp);
	}
	
	showPushNotification(){
		let f = this.constructor.name.concat(".showPushNotification()");
		try{
			let print = false;
			let title = this.getPushNotificationTitle();
			let options = this.getPushNotificationOptions();
			return NotificationData.showPushNotificationStatic(title, options);
		}catch(x){
			error(f, x);
		}
	}
	
	static showPushNotificationStatic(title, options){
		let f = "NotificationData.showPushNotificationStatic()";
		try{
			let print = false;
			if(print){
				window.alert(f+": entered");
			}
			console.trace();
			//console.log(f+": Testing push notification");
			console.log(f+": about to log title");
			console.log(title);
			console.log(f+": about to log options");
			console.log(options);
			if(isWebWorker()){
				self.registration.showNotification(title, options);
			}else{
				console.log(f+": about to call worker.ready.then()");	
				navigator.serviceWorker.ready.then(function(serviceWorkerRegistration){
					let f = "navigator.serviceWorker.ready.then()";
					console.log(f+": service worker is ready; about to show push notification");
					serviceWorkerRegistration.showNotification(title, options);
					console.log(f+": if you did not just see a push notification something bad happened");
				});
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	getPushNotificationTitle(){
		return this.getColumnValue("title");
	}
	
	getNotificationPreview(){
		return this.getColumnValue("preview");
	}
	
	getIcon(){
		return '/images/smiley.png';
	}
	
	getActions(){
		return this.getColumnValue("actions");
	}
	
	getPushNotificationOptions(){
		let f = this.constructor.name.concat(".getPushNotificationOptions()");
		try{
			let serialized = JSON.stringify(this, function(key, value){
				if(this && key === "actions" || key === "responseText"){
					return undefined;
				}
				return value;
			});
			console.log(serialized);
			let options = {
				body:this.getNotificationPreview(),
				icon:this.getIcon(),
				badge:'/images/smiley.png',
				tag:"notification-".concat(this.getNotificationIdSuffix()),
				actions:this.getActions(),
				data:{
					notification:serialized,
					//url:this.getNotificationClickUrl()
				}
			};
			return options;
		}catch(x){
			return error(f, x);
		}
	}
	
	handleNotificationClick(event){
		let f = this.constructor.name.concat(".handleNotificationClick()");
		try{
			switch(event.action){
				case "dismiss":
					submitDismissNotificationForm(this);
					console.log(f+": dismissed notification, I guess");
					break;
				case "":
				case null:
					console.log(f+": nothing to do here");
					openExistingWindow(event, null, this);
					break;
				default:
					return error(f, "Undefined event action \"".concat(event.action).concat("\""));
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	static dismissNotification(event, input){
		let f = "dismissNotification()";
		try{
			let form = input.form;
			let element_id = "notification-".concat(form.getAttribute('uniqueKey'));
			console.log(f.concat(": notification ID is ").concat(element_id));
			if(!elementExists(element_id)){
				return error(f, "Element with ID \"".concat(element_id).concat("\" does not exist"));
			}
			let element = document.getElementById(element_id);
			if(!isset(element)){
				return error(f, "Element is undefined");
			}
			element.style['opacity'] = 0;
			element.style['max-height'] = 0;
			AjaxForm.appendSubmitterName(event, input);
			if(!form.hasAttribute("note_type")){
				return error(f, "Form lacks a note_type attribute");
			}
			let type = form.getAttribute("note_type");
			console.log(f.concat(": dismissed notification has note_type attribute \"").concat(type).concat("\""));
			NotificationData.decrementNotificationCountersStatic(type);
		}catch(x){
			return error(f, x);
		}
	}
	
	static getNotificationCounterIdAttribute(type){
		return "notification_counter-".concat(
			NotificationData.getNotificationTypeStringStatic(type).toLowerCase()
		);
	}
	
	static decrementNotificationCountersStatic(type){
		let f = "NotificationData.decrementNotificationCountersStatic()";
		try{
			//decrement notification filter tab counter for this notification type
				let cid = NotificationData.getNotificationCounterIdAttribute(type);
				let current;
				if(elementExists(cid)){
					let counter = document.getElementById(cid);
					current = parseInt(counter.innerHTML);
					if(current >= 1){
						current--;
					}
					counter.innerHTML = current;
					if(current < 1){
						counter.style['opacity'] = 0;
					}
				}else{
					console.error(f.concat(": notification filter tab counter with ID \"").concat(cid).concat("\" does not exist"));
				}
			//decrement all notifications tab counter
				let all_counter = document.getElementById("label-notification_filter-all-counter");
				current = parseInt(all_counter.innerHTML);
				if(current >= 1){
					current--;
				}
				all_counter.innerHTML = current;
				if(current < 1){
					all_counter.style['opacity'] = 0;
				}
		}catch(x){
			return error(f, x);
		}
	}
	
	hasSubjectKey(){
		return this.hasColumnValue("subjectKey");
	}
	
	getSubjectKey(){
		return this.getColumnValue("subjectKey");
	}
	
	setSubjectKey(tk){
		return this.setColumnValue("subjectKey", tk);
	}
	
	hasSubjectData(){
		return this.hasForeignDataStructure("subjectKey");
	}
	
	getSubjectData(){
		return this.getForeignDataStructure("subjectKey");
	}
	
	setSubjectData(to){
		return this.setForeignDataStructure("subjectKey", to);
	}
	
	static restoreDismissedNotification(form){
		let f = "restoreDismissedNotification()";
		try{
			let element_id = "notification-".concat(form.getAttribute('uniqueKey')); //form.getAttribute("element_id");
			console.log(f.concat(": notification ID is ").concat(element_id));
			let element = document.getElementById(element_id);
			element.style['opacity'] = null;
			element.style['max-height'] = null;
			let type = form.getAttribute("note_type");
			NotificationData.incrementNotificationCountersStatic(type);
			InfoBoxElement.showInfoBox(STRING_NOTIFICATION_DISMISSAL_ERROR);
		}catch(x){
			return error(f, x);
		}
	}
	
	static reinsertNotification(event, button){
		let f = "reinsertNotification()";
		try{
			AjaxForm.appendSubmitterName(event, button);
			let value = button.value;
			let pin_or_unpin;
			switch(value){
				case "pin":
				case "repin":
					pin_or_unpin = "pin";
					break;
				case "unpin":
					pin_or_unpin = 'insert';
					break;
				default:
					console.log(button);
					return error(f, "Invalid button value attribute \"".concat(value).concat("\""));
			}
			let key = button.getAttribute('uniqueKey');
			let notification = document.getElementById("notification-".concat(key));
			if(isset(notification)){
				//create placeholder to reverse reinsertion if XHR fails
					let strawman1 = document.createElement("div");
					strawman1.className = "hidden";
					strawman1.id = "notification_strawman-".concat(key);
					insertAfterElement(strawman1, notification);
				//reinsert notification element at top of list
					let t1id = pin_or_unpin.concat("_notification_here");
					let near_me = document.getElementById(t1id);
					insertAfterElement(notification, near_me);
			}
			//message notification pins also affect conversation label wrappers
			let type = button.getAttribute("note_type");
			if(type == NOTIFICATION_TYPE_MESSAGE){
				let clw = document.getElementById("conversation_label_wrapper-".concat(key));
				if(isset(clw)){
					//create placeholder
						let strawman2 = document.createElement("div");
						strawman2.className = "hidden";
						strawman2.id = "messenger_strawman-".concat(key);
						insertAfterElement(strawman2, clw);
					//reinsert conversation label wrapper
						let t2id = pin_or_unpin.concat("pin_conversation_here");
						let near_me = document.getElementById(t2id);
						insertAfterElement(clw, near_me);
				}
			}
		}catch(x){
			error(f, x);
		}
	}
	
	static restorePinnedNotification(form){
		let f = "restoreDismissedNotification()";
		try{
			
			let key = form.getAttribute('uniqueKey');
			let note_id = "notification-".concat(key);
			let notification = document.getElementById(note_id);
			if(isset(notification)){
				let s1id = "notification_strawman-".concat(key);
				let strawman1 = document.getElementById(s1id);
				insertAfterElement(notification, strawman1);
				removeElementById(s1id);
			}
			let type = form.getAttribute("note_type");
			if(type == NOTIFICATION_TYPE_MESSAGE){
				let clw = document.getElementById("conversation_label_wrapper-".concat(key));
				if(isset(clw)){
					let s2id = "messenger_strawman-".concat(key);
					let strawman2 = document.getElementById(s2id);
					insertAfterElement(clw, strawman2);
					removeElementById(s2id);
				}
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	getNotificationIdSuffixIndex(){
		return 'uniqueKey';
	}
	
	static bindNotificationElement(note_data){
		let f = "NotificationData.bindNotificationElement()";
		try{
			note_data.setColumnValue("widget", "notifications");
			return getApplicationClass()
			.getTypedNotificationClass(note_data.subtype)
			.bindNotificationElement(note_data);
		}catch(x){
			return error(f, x);
		}
	}
	
	static getNotificationTypeStringStatic(type){
		let f = "NotificationData.getNotificationTypeStringStatic()";
		try{
			let aic = getApplicationClass();
			let tnc = aic.getTypedNotificationClass(type);
			console.log(tnc);
			return tnc.getNotificationTypeString();
		}catch(x){
			return error(f, x);
		}
	}
	
	getNotificationTypeString(){
		return NotificationData.getNotificationTypeStringStatic(this.getNotificationType());
	}
	
	updateElement(){
		let f = this.constructor.name.concat(".updateElement()");
		try{
			this.setColumnValue("widget", "notifications");
			let type = this.getNotificationType();
			//let suffix = this.getNotificationIdSuffixIndex();
			let key = this.getNotificationIdSuffix(); //ColumnValue(suffix);
			let id = "notification-".concat(key);
			if(elementExists(id)){//if notification element already exists, update
				let n = NotificationData.bindNotificationElement(this);
				let existing = document.getElementById(id);
				replaceNode(n, existing);
			}else if(NotificationsWidget.isLoaded()){//otherwise, if the notifications list is loaded, insert a new one
				NotificationsWidget.insertNotificationElement(this);
				this.incrementNotificationCounters();
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	incrementNotificationCounters(){
		NotificationData.incrementNotificationCountersStatic(this.getNotificationType());
	}
	
	static incrementNotificationTabCounterStatic(type){
		let f = "NotificationData.incrementTabCounterStatic()";
		try{
			//update category tab counter
			let current;
				let cid = "label-notification_filter-".concat(type).concat("-counter");
				if(elementExists(cid)){
					let counter = document.getElementById(cid);
					current = parseInt(counter.innerHTML);
					current++;
					counter.innerHTML = current;
					counter.style['opacity'] = 1;
				}else{
					console.error(f.concat(": notification filter tab counter with ID \"").concat(cid).concat("\" does not exist"));
				}
			//update all categories tab counter
				let all_counter = document.getElementById("label-notification_filter-all-counter");
				current = parseInt(all_counter.innerHTML);
				current++;
				all_counter.innerHTML = current;
				all_counter.style['opacity'] = 1;
		}catch(x){
			return error(f, x);
		}
	}
	
	static incrementNotificationsIconCounterStatic(){
		let f = "NotificationData.incrementNotificationsIconCounterStatic()";
		try{
			//update widget icon counter
			let icon = document.getElementById("notification_count_icon");
			let current = parseInt(icon.innerHTML);
			current++;
			icon.innerHTML = current;
			icon.style['opacity'] = 1;
		}catch(x){
			return error(f, x);
		}
	}
	
	static incrementNotificationCountersStatic(type){
		let f = "NotificationData.incrementNotificationCountersStatic()";
		try{
			NotificationData.incrementNotificationsIconCounterStatic();
			NotificationData.incrementNotificationTabCounterStatic(type);
		}catch(x){
			return error(f, x);
		}
	}
	
	incrementNotificationTabCounter(){
		return NotificationData.incrementNotificationTabCounterStatic(this.getNotificationType());
	}
	
	getNotificationIdSuffix(){
		return this.getIdentifierValue(); //ColumnValue(this.getNotificationIdSuffixIndex());
	}
	
	getNotificationCount(){
		return this.getColumnValue("notificationCount");
	}
}
