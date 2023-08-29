function cachePageContent(){
	let f = "cachePageContent()";
	try{
		let print = false;
		if(print){
			console.log(f+": about to cache page content");
			console.log("Caching ".concat(document.location));
		}
		let innerHTML = document.getElementById("page_content").innerHTML;
		if(print){
			console.log(f.concat(": caching \"").concat(innerHTML).concat("\""));
		}
		let date = new Date();
		let update = {
			//command:"setInnerHTML",
			//id:"page_content",
			innerHTML:innerHTML,
			/*subcommands:[
				{
					command:"callFunction",
					name:"initializeAllForms"
				}
			]*/
			time:date.getTime()
		};
		try{
			sessionStorage.setItem(document.location, JSON.stringify(update));
			//removeElementById("cache_pg_content_script");
		}catch(x){
			if(x instanceof DOMException && x.name == "QuotaExceededError"){
				console.error(f.concat(": Quota exceeded"));
			}else{
				return error(f, x);
			}
		}
	}catch(x){
		return error(f, x);
	}
}

function expired(time){
	let f = "expired()";
	let print = false;
	if(typeof time == "undefined"){
		return true;
	}else if(typeof time == typeof "string"){
		time = parseInt(time);
	}
	let date = new Date();
	let now = date.getTime();
	let diff = now - time;
	if(diff < (SESSION_TIMEOUT_SECONDS * 1000)){
		if(print){
			console.log(f.concat(": timestaamp ").concat(time).concat(" is recent compared to now ").concat(now));
		}
		return false;
	}else if(print){
		console.log(f.concat(": timestaamp ").concat(time).concat(" is expired compared to now ").concat(now));
	}
	return true;
}

function clearClientCache(event, target){
	sessionStorage.clear();
	//InfoBoxElement.showInfoBox("Cleared cache");
	AjaxForm.appendSubmitterName(event, target);
}

function initializePopState(){
	let f = "initializePopState()";
	try{
		let print = false;
		window.onpopstate = function(event){
			if(print){
				console.log("window popstate event");
			}
			let resource = sessionStorage.getItem(document.location);
			if(print){
				console.log("Reverting to cached page ".concat(document.location));
			}
			let cached = false;
			if(resource){
				let type = typeof resource;
				if(type === 'string'){
					if(print){
						console.log("Cached resource is a string");
					}
					if(!isJson(resource)){
						console.error(f.concat(": \"").concat(resource).concat("\" is not JSON"));
						return;
					}
					resource = JSON.parse(resource);
					if(print){
						printObject(resource);
					}
				} //
				type = typeof resource;
				if(type === "object"){
					cached = true;
					if(print){
						console.log(f.concat(": have to cast the deserialized resource into something useful"));
					}
					let temp;
					if(isset(resource.innerHTML)){
						if(print){
							console.log(f.concat(": resource is a setInnerHTMLCommand"));
						}
						let cmdClass = getApplicationClass().getCommandClass("setInnerHTML");
						resource = new cmdClass({
							command:"setInnerHTML",
							innerHTML:resource.innerHTML,
							id:"page_content",
							subcommands:[{
								command:"callFunction",
								name:"initializeAllForms"
							}]
						}, null);
						resource.execute();
					}else{
						if(print){
							console.log(f.concat(": assuming the resource is a ResponseText"));
						}
						resource = new ResponseText(resource, function(){
							resource.processCommands();
						});
					}
				}else{
					return error(f, "Cached resource is a ".concat(type));
				}
			}
			if(!cached){
				if(print){
					console.log("Nothing cached for page ".concat(document.location));
				}
				window.location.href = document.location;
			}
			if(elementExists("session_timeout_overlay")){
				document.getElementById("session_timeout_overlay").href = document.location;
			}
		};
		if(print){
			console.log(f+": Initialized window.onpopstate");
		}
	}catch(x){
		x(f, x);
	}
}
