function showGenericPushNotification(title, body){
	let f = "showGenericPushNotification()";
	try{
		const options = {
			body:body,
			icon:'smiley.png',
			badge:'smiley.png'
		}
		showPushNotification(title, options);
	}catch(x){
		error(f, x);
	}
}

function isPushSupported(){
	let f = "isPushSupported()";
	try{
		let worker = navigator.serviceWorker;
		if(!isset(worker)){
			let err = "navigator.serviceWorker is undefined";
			console.log(err);
			return false;
		}else if(!('showNotification' in ServiceWorkerRegistration.prototype)){
			console.error(f+": Push notifications are unsupported by your browser");
			return false;
		}else if(!('PushManager' in window)){
			console.log(f+": Push manager is undefined");
			return false;
		}else{
			console.log(f+": push notifications are supported");
			return true;
		}
	}catch(x){
		error(f, x);
	}
}

function isPushEnabled(){
	let f = "isPushEnabled()";
	try{
		let pncb = document.getElementById("push_settings");
		if(!isPushSupported()){
			console.log(f+": Push API is unsupported, so no");
			return false;
		}
		return true;
		/*else if(Notification.permission === 'denied'){
			console.log(f+": The user has blocked notifications.");
			return false;
		}else if(pncb == null){
			console.log(f+": push notifications checkbox does not exist; returning");
			return false;
		}else if(pncb.checked){
			console.log(f+": push notifications are enabled");
			return true;
		}else{
			console.log(f+": user has push notifications disabled");
			return false;
		}*/
	}catch(x){
		error(x);
	}
}

/**
 * generate an input with push subscription key to append to a form.
 * Needed so you don't send notifications to yourself
 */
function generatePushSubscriptionKeyInput(){
	let f = "generatePushSubscriptionKeyInput()";
	try{
		let input = document.createElement("input");
		input.type = "hidden";
		input.name = "pushSubscriptionKey";
		input.value = document.getElementById("pushSubscriptionKey").value;
		return input;
	}catch(x){
		return error(f, x);
	}
}
