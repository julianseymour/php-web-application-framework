class ShortPollForm extends AjaxForm{
	
	static getNotificationDeliveryTimestampValue(){
		let f = "getNotificationDeliveryTimestampValue()";
		try{
			console.log(f+": entered");
			let notify_ts = document.getElementById("notify_ts");
			if(!isset(notify_ts)){
				console.error("Notification delivery timestamp element is undefined");
				return null;
			}
			
			let v = notify_ts.value;
			let err;
			if(v == 0){
				err = "Notification delivery timestamp is undefined";
			}else{
				err = "Notification delivery timestamp is ".concat(v);
			}
			console.log(err);
			console.log(f+": returning ".concat(v));
			return v;
		}catch(x){
			console.trace();
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
	
	static getUpdateCheckInterval(){
		let f = "getUpdateCheckInterval()";
		try{
			return document.getElementById("update_interval").value;
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			return null;
		}
	}

	static setUpdateCheckInterval(interval){
		let f = "getUpdateCheckInterval()";
		try{
			return document.getElementById("update_interval").value = interval;
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			ShortPollForm.clearMessageUpdater();
		}
	}

	static scheduleUpdateCheck(){
		let f = "scheduleUpdateCheck()";
		try{
			//return;
			let interval = ShortPollForm.getUpdateCheckInterval();
			if(interval == null){
				console.log(f+": update check interval is undefined; clearing timeout function");
				ShortPollForm.clearMessageUpdater();
				return;
			}
			console.log(f+": entered; about to set timeout");
			//window.messageUpdater = setTimeout(ShortPollForm.poll, interval);
			console.log(f+": returning normally");
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}

	static getNotificationDeliveryTimestampElement(){
		return document.getElementById("notify_ts");
	}
	
	static poll(){
		let f = "poll()";
		try{
			/*console.log(f+': entered');
			console.log(f+": about to get message update check timestamp");
			let msg_ts = SendMessageForm.getMessageUpdateTimestamp();
			if(msg_ts == null){
				console.log(f+": message update timestamp is null, skipping message check");
			}else{
				console.log(f+": message update check timestamp is ".concat(msg_ts));
			}
			console.log(f+": about to get notification update check timestamp");
			let notify_ts = ShortPollForm.getNotificationDeliveryTimestampValue();
			console.log(f+": notification delivery timestamp is ".concat(notify_ts));	
			console.log(f+": about to create new xhr");
			let onerror = function(){
				let f = "onerror";
				console.error(f+": XHR error during message update check");
				console.trace();
				return;
			}
			let action = document.getElementById('notify_form').getAttribute('action');
			//xhr.open("POST", action, true);
			//xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			let user_key = document.getElementById("user_key").value;
			let params = "directive=read_multiple&userKey="+user_key+"&notify_ts="+notify_ts;
			if(msg_ts != null){
				params += "&msg_ts=".concat(msg_ts);
				params += "&correspondentKey=".concat(SendMessageForm.getCorrespondentKey());
				params += "&correspondentAccountType=".concat(SendMessageForm.getCorrespondentAccountType());
			}
			fetch_xhr("POST", action, params, function(response){
				if(response.hasCommands()){
					console.log(f+": there are media commands to process after update check");
					response.processCommands();
				}else{
					console.log(f+": no media commands to process after update check");
				}
			}, onerror, getContentTypeString());
			//console.log(f+": about to send XHR with params \"".concat(params+"\""));
			console.log(f+": sent xhr for message update");*/
			let form = document.getElementById("notify_form"); //.dispatchEvent(new Event("submit"));
			AjaxForm.submitForm(form, controller, error_cb);
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
	
	static clearMessageUpdater(){
		let f = "clearMessageUpdater()";
		try{
			//console.log(f+": about to remove message update event handler");
			//document.getElementById("notify_form").removeEventListener("check_update", MessengerElement.checkMessageUpdate);
			console.log(f+": about to clear message update checker");
			window.clearTimeout(window.messageUpdater);
			console.trace();
			window.alert(f+": Killed update checker");
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
}