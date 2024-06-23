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
			let form = document.getElementById("notify_form");
			AjaxForm.submitForm(form, controller, error_cb);
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
	
	static clearMessageUpdater(){
		let f = "clearMessageUpdater()";
		try{
			console.log(f+": about to clear message update checker");
			window.clearTimeout(window.messageUpdater);
			console.trace();
			window.alert(f+": Killed update checker");
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
}