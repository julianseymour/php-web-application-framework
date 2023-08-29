
//async.js

const serviceWorkerCacheName = 'swcache';

async function safeScrollToBottom(){
	let f = "safeScrollToBottom()";
	try{
		let correspondentKey = SendMessageForm.getCorrespondentKey();
		//document.getElementById("conversation-".concat(correspondentKey)).onscroll = null;
		let scrold = await ConversationThreadElement.updateMessageScroll();
	}catch(x){
		console.error(f+" error scrolling to bottom: "+x.toString());
		console.trace();
		return;
	}
}

const subscribe = async function(){
	let f = "subscribe()";
	try{
		let print = false;
		if(print){
			window.alert(f.concat(": entered"));
		}
		navigator.serviceWorker.ready.then(
			async function(registration){
				let subscribeOptions = {
					userVisibleOnly: true,
					applicationServerKey: urlBase64ToUint8Array(PUSH_API_SERVER_PUBLIC_KEY)
				};
				return registration.pushManager.subscribe(subscribeOptions);
			}
		).then(function(subscription){
			if(print){
				window.alert('Subscribed', subscription.endpoint);
			}
			let stringified = JSON.stringify({
				subscription: subscription
				//js: 1
			});
			if(print){
				window.alert(f+": about to fetch from subscribe");
			}
			return fetch('/subscribe/', {
				method:'post',
				headers:{
					'Content-type':'application/x-www-form-urlencoded',
					'X-Requested-With':'fetch'
				},
				credentials:'include',
				body: stringified
			}).then(function(response){
				if(!isset(response) || response == ""){
					console.error(f+": response is null or empty string");
					return null;
				}
				if(print){
					window.alert(f+": got response");
				}
				if(!response.ok){
					console.error(f+": response is not OK");
					window.alert(response);
					throw new Error('Bad status code from server.');
				}else if(print){
					window.alert(f+": server response is OK");
				}
				if(print){
					window.alert(f+": about to get JSON from response");
				}
				return response.text().then(function(json){
					if(!isset(json) || json == ""){
						console.error(f+": JSON is undefined; about to echo");
						console.log(response);
						return null;
					}else if(!isJson(json)){
						let x = "Whatever it is ain't JSON";
						window.alert(json);
						return error(f, x);
					}
					let notify_form = document.getElementById("notify_form");
					notify_form.setAttribute("initialized", 1);
					let decoded = JSON.parse(json);
					document.getElementById("pushSubscriptionKey").value = decoded.pushSubscriptionKey;
					if(print){
						window.alert(f+": returning json");
						window.alert(json);
					}
					console.log(f.concat("Push subscription was successful"));
					return json;
				}).catch(function(x){
						error(f, x);
						window.alert(response);
						window.alert(f+": caught error with response.text()");
						return null;
				});
			}).catch(function(x){
				error(f, x);
				window.alert(response);
				window.alert(f+": caught error");
				return null;
			});
		});
	}catch(x){
		error(f, x);
	}
}

function unsubscribe(){
	let f = "unsubscribe()";
	try{
		navigator.serviceWorker.ready.then(function(registration){
			return registration.pushManager.getSubscription();
		}).then(function(subscription){
			return subscription.unsubscribe().then(function(){
				console.log(f+': Unsubscribed', subscription.endpoint);
				return fetch('/unsubscribe', {
					method:'post',
					headers:{
						'Content-type': 'application/x-www-form-urlencoded'
					},
					credentials:'include',
					body: JSON.stringify({
						subscription: subscription
					})
				});
			});
		});
	}catch(x){
		error(f, x);
	}
}

const askPermission = async function(){
	const f = "askPermission()";
	try{
		return new Promise(function(resolve, reject){
			const permissionResult = Notification.requestPermission(function(result){
				resolve(result);
			});
			if(permissionResult){
				permissionResult.then(resolve, reject);
			}
		}).then(function(permissionResult){
			if(permissionResult !== 'granted'){
				throw new Error('Permission denied');
			}
		});
	}catch(x){
		error(f, x);
	}
}

const getNotificationPermissionState = async function(){
	const f = "getNotificationPermissionState()";
	try{
		if(navigator.permissions){
			return navigator.permissions.query({name: 'notifications'}
		).then(
			(result) => {
				return result.state;
			});
		}
		return new Promise((resolve) => {
			condole.log(f+": async.js line 164");
			resolve(Notification.permission);
		});
	}catch(x){
		error(f, x);
	}
}

const sendMessageToServiceWorker = function(message){
	const f = "sendMessageToServiceWorker()";
	try{
		let print = false;
		return new Promise(function(resolve, reject){
			if(print){
				console.log(f+": inside promise");
			}
			let messageChannel = new MessageChannel();
			messageChannel.port1.onmessage = function(event){
				if(print){
					console.log(f+": inside messageChannel.port1.onmessage");
				}
				if(event.data.error){
					if(print){
						console.log(f+": event.data.error")
					}
					reject(event.data.error);
				}else{
					if(print){
						console.log(f+": about to call resolve(event.data)");
					}
					resolve(event.data);
				}
			};
			if(print){
				console.log(f+": about to log navigator.serviceWorker.controller");
				console.log(navigator.serviceWorker.controller);
				console.log(f+": about to call navigator.serviceWorker.controller.postMessage()");
			}
			if(!isset(navigator.serviceWorker.controller)){
				console.error(f+": navigator.serviceWorker.controller is undefined");
			}else{
				navigator.serviceWorker.controller.postMessage(message, [messageChannel.port2]);
			}
		});
	}catch(x){
		error(f, x);
	}
}

const initializeBackgroundSync = async function(){
	let f = "initializeBackgroundSync()";
	try{
		let print = false;
		if(print){
			window.alert(f+": entered");
		}
		let notify_form = document.getElementById("notify_form");
		if(!isset(notify_form)){
			let x = "notification update check form is undefined";
			return error(f, x);
		}
		let init = notify_form.getAttribute("initialized");
		if(init === 1){
			if(print){
				window.alert(f+": notification update form was already initialized");
			}
			return;
		}else if(print){
			window.alert(f+": initializing update check form");
		}
		notify_form.addEventListener('check_update', ShortPollForm.poll);
		if(print){
			window.alert(f+": added update check event listener");
		}
		if(!isPushEnabled()){
			if(print){
				window.alert(f+": push notifications are not enabled -- returning");
			}
			return;
		}
		if(!isset(navigator.serviceWorker)){
			if(print){
				window.alert(f+": navigator.serviceWorker is undefined");
			}
			return;
		}else if(typeof window.Notification === 'undefined'){
			if(print){
				window.alert(f+": looks like push notifications are unsupported on your device");
			}
			return;
		}
		//this is where all functions for interacting with the UI from within the service worker must go
		navigator.serviceWorker.addEventListener('message', function(event){
			if(print){
				window.alert(f+": about to log event");
				window.alert(event);
			}
			if(isset(event.data)){
				if(print){
					window.alert(f+": about to process media commands sent from service worker");
				}
				switch(event.data.intent){
					case "notificationclick":
						if(print){
							window.alert(f+": notification clicked -- you are now inside message event handler");
						}
						let note_data = NotificationData.allocateNotificationDataStatic(
							event.data, 
							null
						);
						note_data.handleNotificationClick(event);
						break;
					default:
						if(print){
							window.alert(f+": about to log event.data");
							window.alert(event.data);
							let type = typeof event.data;
							window.alert(f.concat(": typeof event.data is \"").concat(type).concat("\""));
						}
						let callback = function(rt){
							console.log(f.concat(": still inside initializeBackgroundSync. About to handle message event"));
							getApplicationClass().handleMessageEvent(rt);
							console.log(f.concat(": called handleMessageEvent"));
						};
						let response = new ResponseText(event.data, callback);
						break;
						//return error(f, "Undefined intent \"".concat(event.data.intent).concat("\""));
				}
			}else if(print){
				window.alert(f+": no media commands to process from service worker");
			}
		});
		
		if(print){
			window.alert(f+": added message event listener; about to register service worker");
		}
		
		navigator.serviceWorker.register('/script/'.concat(LOCALE).concat('/service-worker.js'));
		
		if(print){
			window.alert(f+": called navigator.serviceWorker.register");
		}
		navigator.serviceWorker.ready.then(async function(registration){
			if(print){
				window.alert(f.concat(': service worker ready'));
			}
			let sub;
			try{
				const permission = await window.Notification.requestPermission();
				if(permission !== 'granted'){
					console.error(f.concat('Permission not granted for Notification'));
				}
				sub = registration.pushManager.getSubscription();
			}catch(x){
				console.error(f+": exception thrown requesting permission");
				if(x instanceof TypeError){
					window.alert(f+": lucky for you it's a TypeError, most likely the user is a Safari-using chump");
					Notification.requestPermission(() => {
						sub = registration.pushManager.getSubscription();
					});
				}else{
					return error(f, x);
				}
			}
			if(print){
				window.alert(f+": returning normally");
			}
			return sub;
		}).then(async function(subscription){
			if(print){
				window.alert(f+": attempted to subscribe, about to check whether a subscription already existed beforehand");
			}
			if(subscription){
				if(print){
					window.alert(f+': Already subscribed', subscription.endpoint);
				}
				//unsubscribe();
			}else if(print){
				window.alert(f+": no, you were not already subscribed");
			}
			let json = subscribe();
		}).catch(function(x){
			console.error(f+": caught exception readying subscription");
			error(f, x);
		});
		if(print){
			window.alert(f+": returning");
		}
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}
