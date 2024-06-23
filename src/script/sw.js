	//self.clients.matchAll().then(function(clients){
		//clients.forEach(function(client){
			//console.log(client);
			//client.postMessage('The service worker just started up.');
		//});
	//});
	
	//Time limited network request. If the network fails or the response is not served before timeout, the promise is rejected.
	/*function fromNetwork(request, timeout){
		return new Promise(function (fulfill, reject){
			//Reject in case of timeout.
			let timeoutId = setTimeout(reject, timeout);
			//Fulfill in case of success.
			fetch(request).then(function (response){
				clearTimeout(timeoutId);
				fulfill(response);
				//Reject also if network fetch rejects.
			}, reject);
		});
	}*/
	
	/*function getEndpoint(){
		return self.registration.pushManager.getSubscription().then(
			function(subscription){
				if(subscription){
					return subscription.endpoint;
				}
				throw new Error('User not subscribed');
			}
		);
	}*/
	
	const sendMessageToController = function(arr){
		const f = "sendMessageToController()";
		try{
			self.clients.matchAll().then(function(clients){
				clients.forEach(function(client){
					console.log(client);
					client.postMessage(arr);
				});
			});
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
	
	//Register event listener for the ‘push’ event.
	//console.log("About to register push event listener");
	self.addEventListener('push', function(event){
		let f = "serviceWorker.push()";
		try{
			let print = false;
			if(print){
				console.log(f+": Inside the service worker's push event listener");
			}
			if(event.data){
				if(print){
					console.log(f+": event data is defined");
				}
				const rt = event.data.text();
				if(!isset(rt)){
					console.error(f+": response text is undefined");
					return;
				}else if(!isJson(rt)){
					console.error(f+": response text is not JSON");
					console.log(rt);
					return;
				}else if(print){
					console.log(f+": response text is JSON");
				}
				let data = JSON.parse(rt);
				event.waitUntil(
					fetch("/fetch_update/", {
						method:'post',
						headers:{
							'Content-Type':'application/x-www-form-urlencoded',
							'X-Requested-With':'fetch'
						},
						body:JSON.stringify({
							num_cipher_64:data.num_cipher_64,
							user_key_cipher_64:data.user_key_cipher_64,
							username:data.username
						})
					}).then(function(response){
						return response.json().then(function(data){
							let callback = function(rt){
								let print = false;
								if(print){
									console.log(f.concat(": Inside callback"));
								}
								self.clients.matchAll({includeUncontrolled: true, type:"window"}).then(function(clients){
									let include_body = false;
									if(clients.length > 0){
										if(print){
											console.log(f.concat(": there are ").concat(clients.length).concat(" clients to notify"));
										}
										include_body = true;
									}else {
										if(print){
											console.log(f.concat(": there are no window clients"));
										}
										let n = rt.getPushedNotificationData();
										let title = n.getPushNotificationTitle();
										let options = n.getPushNotificationOptions();
										self.registration.showNotification(title, options);
									}
									rt.intent = "handle_fetch";
									if(print){
										console.log(f+": about to log response text");
										console.log(rt);
									}
									if(include_body){
										if(print){
											console.log(f+": about to call sendClientCommand");
										}
										clients.forEach(function(client){
											sendClientCommand(client, rt);
										});
									}
								});
							}
							let r = new ResponseText(data, callback);
						});
					})
				);
			}else{
				console.error(f+": event.data is undefined");
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			console.log(f+": exception thrown in push event listener");
		}
	});
	console.log("Registered push event listener");
	
	/**
	 * a wrapper for fetch_xhr that iterates through all clients and has them create a ResponseText 
	 * object which will have a clue about which callback to invoke through the intent; 
	 * this does not work for push notifications because self.registration.showNotification must be 
	 * called instead of navigator.serviceWorker.ready().then(function(reg){reg.showNotification(...)} in
	 * order to see push notifications after closing the tab or before resubscribing 
	 */
	const fetch_client = function(action, body_unserialized, intent, callback_error=null){
		let f = "fetch_client()";
		try{
			let print = false;
			self.clients.matchAll({type:"window"}).then(function(clients){
				let include_body = false;
				if(clients.length > 0){
					console.log(f.concat(": there are ").concat(clients.length).concat(" clients to notify"));
					include_body = true;
				}else{
					console.log(f.concat(": there are no window clients"));
				}
				let callback_success = function(rt){
					if(!isset(rt)){
						return console.error(f+": response text is undefined");
					}
					rt.intent = intent;
					if(include_body){
						if(print){
							console.log(f+": about to call sendClientCommand");
						}
						clients.forEach(function(client){
							sendClientCommand(client, rt);
						});
					}else{
						console.error(f.concat(": unimplemented: process response without body for intent \"").concat(intent).concat("\""));
					}
				}
				fetch_xhr("POST", action, body_unserialized, callback_success, error_cb);
			});
		}catch(x){
			error(f, x);
		}
	}
	
	self.addEventListener('install', function(event){
		window.alert("Service worker installed");
		event.waitUntil(self.skipWaiting()); // Activate worker immediately
	});
	
	self.addEventListener('activate', function(event){
		window.alert("Service worker activated");
		event.waitUntil(self.clients.$this->claim()); // Become available to all pages
	});
	
	/*self.addEventListener('periodicsync', function(event){
		if(event.registration.tag == 'my-tag'){
			event.waitUntil(
				fireUpdateCheckEvent() //doTheWork()// "do the work" asynchronously via a Promise.
			); 
		}else{
			// unknown sync, may be old, best to unregister
			event.registration.unregister();
		}
	});*/
	
	//Listen to pushsubscriptionchange event which is fired when subscription expires. Subscribe again and register the new subscription in the server by sending a POST request with endpoint. Real world application would probably use also user identification.
	self.addEventListener('pushsubscriptionchange', function(event){
		let f = "pushsubscriptionchange";
		try{
			let print = false;
			if(print){
				console.log('Subscription expired');
			}
			event.waitUntil(
				self.registration.pushManager.subscribe({
					userVisibleOnly:true
				}).then(function(subscription){
					if(print){
						console.log('Subscribed after expiration', subscription.endpoint);
					}
					return fetch('/subscribe/',{
						method: 'post',
						headers:{
							'Content-type':'application/x-www-form-urlencoded',
							'X-Requested-With':'fetch'
						},
						body:JSON.stringify({
							endpoint: subscription.endpoint
						})
					});
				})
			);
		}catch(x){
			error(f, x);
		}
	});
	
	/*function sendControllerCommand(media=null){//command, body=null){
		let f = "sendControllerCommand()";
		try{
			console.log(f+": entered");
			let message = getCommandMessageBody(media); //command, body);
			sendMessageToController(message);
			console.log(f+": returning normally");
		}catch(x){
			console.error(f+": exception");
			console.trace();
		}
	}*/
	
	function sendClientCommand(client, data=null){
		let f = "sendClientCommand()";
		try{
			let print = false;
			if(print){
				console.log(f+": entered");
			}
			if(isset(data)){
				if(print){
					console.log(f+": body is defined");
					console.log(data);
				}
				if(data.intent == "handle_fetch" && empty(data.dataStructures)){
					console.error(f+": command's intent is to handle a fetch event, but the data is empty");
					return;
				}else if(print){
					console.trace();
				}
			}else if(print){
				console.log(f+": body is undefined");
			}
			//console.log(f+": about to get command message body");
			//let message = getCommandMessageBody(data); //"".concat(command), body);
			if(print){
				console.log(f.concat(": about to post message"));
			}
			client.postMessage(data); //ssage);
			if(print){
				console.log(f+": returning normally");
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			console.trace();
		}
	}
	
	/*let alertController = function(errmsg){
		let f = "alertController()";
		try{
			sendControllerCommand("alert", errmsg);
		}catch(x){
			console.error(f+": exception thrown");
		}
	}*/
	
	const openExistingWindow = function(event, urlToOpen=null, data=null){//, intent=null){//}, command=null){ //command=null, data=null){
		const f = "openExistingWindow()";
		try{
			const promiseChain = clients.matchAll({
				type:'window',
				includeUncontrolled:true
			}).then((windowclients) => {
				let matchingClient = null;
				for(let i = 0; i < windowclients.length; i++){
					const windowClient = windowclients[i];
					if(urlToOpen == null || windowClient.url === urlToOpen){
						matchingClient = windowClient;
						break;
					}
				}
				if(matchingClient){
					matchingClient.focus();
					//return matchingClient;
					//if(data != null){//command != null){
						//data.intent = intent;
						console.log(f+": machingClient and data are defined; about to sent client command");
						sendClientCommand(matchingClient, data);
					//}else{
					//	console.log(f+": not sending a client command");
					//}
				}else{
					clients.openWindow(urlToOpen).then(function(windowClient){
						console.log(f+": inside then of promise returned by clients.openWindow");
						//windowClient.focus();
						sendClientCommand(windowClient, data);
					});
				}
			});
			event.waitUntil(promiseChain);
		}catch(x){
			console.error(f+": caught exception");
		}
	}
	
	self.addEventListener('notificationclick', function(event){
		let f = "notificationclick()";
		try{
			console.log(f+": Notification clicked");
			//console.log(f+": about to switch notification action command");
			console.log(event);
			console.log(event.notification.data);
			//let note_data = NotificationData.allocateNotificationDataStatic(JSON.parse(event.notification.data), null);
			//note_data.handleNotificationClick(event);
			let note_data = JSON.parse(event.notification.data.notification);
			//data.intent = event.action;
			note_data.action = event.action;
			note_data.intent = "notificationclick";
			//switch(event.action){
				/*case null:
				case "":
					openExistingWindow(event, event.notification.data.url, note_data);
					break;*/
				//default:
					//console.log(f+": notification action is something other than empty string");
					note_data = NotificationData.allocateNotificationDataStatic(note_data, null);
					note_data.handleNotificationClick(event);
					//break;
			//}
			event.notification.close();
		}catch(x){
			let err = f.concat(": Exception thrown in notificationclick event handler");
			console.error(err);
			return error(f, x);
		}
	}, false);
	
	try{
		self.addEventListener('message', function(event){
			let f = "ServiceWorker.onmessage";
			try{
				let print = false;
				if(print){
					console.log(f+": received a message event");
					console.log(f+": about to log data");
					console.log(event.data);
					console.log(f+": about to log source ID");
					console.log(event.source.id);
				}
				if(typeof event.data == "string"){
					switch(event.data){
						case "reset_timeout":
							let rt = {intent:"reset_timeout"};
							self.clients.matchAll({type:"window"}).then(function(clients){
								clients.forEach(function(client){
									if(client.id !== event.source.id){
										if(print){
											console.log(f+": about to issue a reset session timeout command");
										}
										sendClientCommand(client, rt);
									}else{
										if(print){
											console.log(f+": client is the source -- skipping reset timeout");
										}
										//continue;
									}
								});
							});
							break;
						default:
							if(print){
								console.log(f+": event.data is something besides an instruction to reset session timeout");
							}
							break;
					}
				}else if(print){
					console.log(f+": event.data is something other than a string");
					console.log(event.data);
				}
				//console.log(event);
			}catch(x){
				let err = f.concat(": Exception thrown in ServiceWorker.onmessage event handler");
				console.error(err);
				return error(f, x);
			}
		}, false);
	}catch(x){
		let err = "There's something wrong with your service worker message event listener: \"".concat(x.toString()).concat("\"");
		console.error(err);
	}
	
	self.addEventListener('install', (event) => {
		console.log('Service worker installed');
		event.waitUntil((async () => {
			const cache = await caches.open(serviceWorkerCacheName);
			console.log('Caching offline content...');
			await cache.addAll(serviceWorkerCacheContent);
		})());
	});
	
	/*self.addEventListener('fetch', (event) => {
		event.respondWith((async () => {
			const resource = await caches.match(event.request);
			console.log(`Fetching resource ${event.request.url}`);
			if(resource){
				return resource;
			}
			const response = await fetch(event.request);
			const cache = await caches.open(serviceWorkerCacheName);
			console.log(`Caching ${event.request.url}`);
			cache.put(event.request, response.clone());
			return response;
		})());
	});*/
	
	self.addEventListener('activate', (event) => {
			event.waitUntil(caches.keys().then((keys) => {
				return Promise.all(keys.map((key) => {
					if(key === serviceWorkerCacheName){
						return;
					}
					return caches.delete(key);
				}))
			}));
		}
	);
	
	/*self.addEventListener('notificationclose', function(event){
		let f = "notificationclose";
		try{
			console.trace();
			console.log(f+": not implemented");
		}catch(x){
			let err = f.concat(": Exception thrown in notificationclose");
			console.error(err);
		}
	});*/
	//service workers suck