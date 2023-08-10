'use strict';

function isNode(o){
	return(
		typeof Node === "object" ? o instanceof Node 
		: o && typeof o === "object" 
			&& typeof o.nodeType === "number" 
			&& typeof o.nodeName === "string"
	);
}

function isPromise(obj){
	return typeof obj.then == 'function';
}

function getContentTypeString(){
	return "application/x-www-form-urlencoded";
}

function parseTimestamp(timestamp){
	const f = "parseTimestamp()";
	try{
		const date = new Date(timestamp * 1000);
		return date.toDateString().concat(" "+date.toLocaleTimeString());
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function insertBeforeMultiple(referenceNode, ...insertedNodes){
	const f = "insertBeforeMultiple()";
	try{
		for(let node of insertedNodes){
			referenceNode.parentNode.insertBefore(node, referenceNode);
		}
	}catch(x){
		return error(f, x);
	}
}

function hydrateElement(element_array, responseText){
	const f = "hydrateElement()";
	try{
		let print = false;
		if(!isset(element_array)){
			return error(f, "Element array is null");
		}else if(typeof element_array.tag == "undefined"){
			console.log(element_array);
			error(f, "Undefined element tag");
			
			return null;
		}else if(print){
			console.log(f.concat(": Entered"));
			console.log(element_array);
		}
		let tag = element_array.tag;
		let element = null;
		if(tag === "!--"){
			if(print){
				console.log(f+": element is an HTML comment");
			}
			let comment = document.createComment(element_array.innerHTML);
			if(print){
				console.log(comment);
			}
			return comment;
		}else if(tag === "fragment"){
			if(print){
				console.log(f+": element is a document fragment");
			}
			element = new DocumentFragment();
		}else{
			if(print){
				console.log(f.concat(": about to create an element with tag \"").concat(tag).concat("\""));
				if(tag === "a"){
					console.log(f+": about to create an anchor element");
				}
			}
			element = document.createElement(tag);
		}
		if(!isset(element)){
			let err = "Element is undefined immediately after declaration";
			console.error(element);
			if(element instanceof Element){
				let err = "isset is not working";
				return error(f, err);
			}
			return error(f, err);
		}
		if(print){
			console.log(f+": about to assign attributes");
		}
		//assign attributes
			if(isset(element_array.attributes)){
				let attributes = element_array.attributes;
				if(attributes instanceof NamedNodeMap){
					return error(f, "Attributes array is an instanceof NamedNodeMap");
				}
				if(print){
					console.log(f+": about to log just the attributes");
					console.log(attributes);
				}
				let attribute;
				for(let key in attributes){
					if(isset(attributes[key])){
						if(typeof attributes[key] == "object"){
							if(print){
								console.log(f.concat(": Attribute with key \"").concat(key).concat("\" is an object"));
							}
							let attribute = element_array.attributes[key];
							if(print){
								console.log(attribute);
							}
							let command = Command.createCommand(attributes[key], responseText);
							if(print){
								console.log(f+": about to evaluate the following media command");
								console.log(command);
							}
							attribute = command.evaluate();
							if(print){
								console.log(f+": evaluated command");
							}
						}else{
							if(print){
								console.log(f+": attribute is an ordinary value");
							}
							attribute = attributes[key];
						}
					}else{
						if(print){
							console.log(f.concat(": attribute \"").concat(key).concat("\" does not have a value"));
							console.log(element_array);
						}
						attribute = "";
					}
					if(print){
						let err = f.concat(": about to set attribute \"").concat(key).concat("\" to \"").concat(attribute).concat("\"");
						console.log(err);
					}
					if(print){
						console.log(f.concat(": about to deal with textarea values"));
					}
					if(tag === "textarea"){
						if(key === "value"){
							if(print){
								console.log(f+": element is a textarea, and the key is value");
								console.log(attribute);
							}
							let deferred_attribute = attribute;
							defer(function(){
								if(print){
									console.log(deferred_attribute);
								}
								element.value = deferred_attribute;
							}); //needed to avoid InvalidStateError
						}else{
							if(print){
								console.log(f.concat(": attribute \"").concat(key).concat("\" is not value"));
							}
							element.setAttribute(key, attribute);
						}
					}else{
						if(print){
							console.log(f.concat(": element is a ").concat(tag).concat(", not a textarea"));
						}
						element.setAttribute(key, attribute);
					}
					if(print){
						console.log(f+": assigned attribute");
					}
				}
			}
			if(print){
				console.log(f+": done assigning regular attributes; about to deal with responsive style properties");
			}
		//assign style properties that requies runtime information to determine
			if(isset(element_array.responsiveStyleProperties)){
				for(let key in element_array.responsiveStyleProperties){
					let property = element_array.responsiveStyleProperties[key];
					console.log(f+": about to create a responsive style property");
					let command = Command.createCommand(property, responseText);
					element.style[key] = command.evaluate(); //processGet/ValueCommand();
				}
			}
			if(print){
				console.log(f+": done assigning responseive style properties; about to deal with child nodes/innerHTML");
			}
		//generate innerHTML/child nodes
			if(isset(element_array.script_uri)){
				if(print){
					//console.log("Setting element src to ".concat(element_array.script_uriaction
				}
				element.src = "/script/".concat(element_array.script_uri);
			}else if(typeof element_array.innerHTML != "undefined"){
				if(print){
					console.log(f.concat(": Element has defined innerHTML"));
				}
				if(typeof element_array.innerHTML == "object"){
					window.alert(f+": element inner HTML is an object");
				}else{
					if(print){
						console.log(f+": innerHTML is defined for this element");
						console.log(element_array.innerHTML);
					}
					if(!isset(element_array.innerHTML)){
						return error(f, "innerHTML is undefined");
					}
					element.innerHTML = element_array.innerHTML;
				}
			}else if(isset(element_array.childNodes)){
				if(print){
					console.log(f+": about to log childNodes");
					console.log(element_array.childNodes);
				}
				for(let i = 0; i < element_array.childNodes.length; i++){
					if(typeof element_array.childNodes[i] === "object"){
						if(print){
							console.log(f+": child node at index \"".concat(i).concat("\" is an object"));
						}
						let child_array = element_array.childNodes[i];
						if(!isset(child_array)){
							console.error(f.concat(": child array is undefined at index ").concat(i).concat(" -- about to log parent node's array"));
							console.error(element_array);
							return error(f, "Child array is undefined");
						}else if(typeof child_array.tag == "undefined"){
							console.error(element_array);
							error(f, "Child element lacks a tag. Logged the parent.");
							return null;
						}else if(print){
							console.log(f+": about to call this function recursively on the following pile of crap:");
							console.log(child_array);
						}
						
						let child = hydrateElement(child_array, responseText);
						if(!isset(child)){
							let err = "hydrateElement returned null";
							console.error(err);
							return error(f, err);
						}else if(print){
							console.log(child);
							console.log(f+": about to append the child node we just logged");
						}
						element.appendChild(child);
						if(print){
							console.log(f+": inserted a parsed child node");
						}
					}else{
						if(print){
							console.log(f+": child node is a string, I certainly hope, because it's getting inserted as is");
							console.log(element_array.childNodes[i]);
						}
						if(i == 0){
							element.innerHTML = element_array.childNodes[i];
						}else{
							element.innerHTML += element_array.childNodes[i];
						}
					}
				}
			}else if(print){
				console.log(f+": none of the above");
			}
		if(!isset(element)){
			let err = "element is undefined at the bottom of the function";
			return error(f, err);
		}
		if(print){
			console.log(f+": done dealing with innerHTML; returning normally");
		}
		return element;
	}catch(x){
		console.log(element_array);
		return error(f, x);
	}
}

function printInnerHTMLById(id){
	const f = "printInnerHTMLById";
	try{
		let printme = document.getElementById(id).innerHTML;
		let windy = window.open();
		windy.document.write(printme);
		windy.print();
		windy.close();
	}catch(x){
		let err = f.concat(" error: \"").concat(x).concat("\"");
		console.error(err);
		console.trace();
	}
}

function getCurrentUserAccountType(){
	return document.getElementById("account_type").value;
}

function getViewportWidth(){
	return Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
}

function getViewportHeight(){
	return Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
}

function parseTimeStringFromTimestamp(timestamp){
	const f = "parseTimeStringFromTimestamp(".concat(timestamp).concat(")");
	try{
		let date = new Date(timestamp * 1000);
		return date.toLocaleTimeString();
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function parseDateStringFromTimestamp(timestamp){
	const f = "parseDateStringFromTimestamp(".concat(timestamp).concat(")");
	try{
		let date = new Date(timestamp * 1000);
		return date.toDateString();
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function elementExists(id){
	let element = document.getElementById(id);
	return typeof(element) != 'undefined' && element != null;
}

function getChildNodeByClass(n, c){ //get all child nodes of node n that are class c
	const f = "getChildNodeClass(~, "+c+")";
	try {
		let len = n.childNodes.length;
		//console.log(f+":\n\tEntered for a node with "+len.toString()+" children");
		let get = [];
		for (let i = 0; i < len; i++) {
			//console.log(f+": iteration "+i.toString());
			let cl = n.childNodes[i].className;
			if (n.childNodes[i].className == c){
				//console.log("\tPushed node "+i.toString()+"\n");
				get.push(n.childNodes[i]);
			}
		}
		return get;
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
		return null;
	}
}

function replaceInnerHTMLById(id, html, callback_success, callback_error){ //gracefully transition between two elements 
	const f = "replaceInnerHTMLById()";
	try {
		if (typeof html == 'undefined') {
			let err = f+" error: html is undefined";
			console.error(err);
			console.trace();
			//console.log(err);
			return false;
		}else if (html === ""){
			console.error(f+": html is empty string");
			return false;
		}
		//console.log(f+": entered with html \""+html+"\"");
		//console.log(f+" html is of type "+typeof html);
		let ce = document.getElementById(id);		
		if (ce == null) {
			console.error(f+": element by id \""+id+"\"");
			return false;
		} else {
			replaceInnerHTML(ce, html, callback_success, callback_error);
		}
	} catch(x) {
		console.error(f+" exception: "+x.toString());
		return false;
	}
}

/**
 * defer a function call to prevent race condition
 * @param timeout
 * @returns
 */
function defer(timeout, delay=null){
	const f = "defer()";
	try{
		if(delay == null){
			delay = 0;
		}
		setTimeout(function(){
			//console.log(f+": about to call timeout()");
			timeout();
			//console.log(f+": called timeout()");
		}, delay);
	}catch(x){
		console.error(f+" exception: "+x.toString());
		console.trace();
	}
}

function transitionEndHandler(element, callback_first, timeout_second, listener) {//execute the callback once the transition has ended, remove the transitionend event listener, then defer timeout if it exists
	const f = "transitionEndHandler()";
	try {
		//console.log(f+": entered; about to remove transition end listener");
		//defer(timeout_second); //revealHiddenElement(element, callback_success, old_opacity);
		element.removeEventListener("transitionend", listener);
		//console.log(f+": removed transition end listener");
		if(typeof callback_first == 'function'){
			//console.log(f.concat(": Callback is defined"));
			//setTimeout(function(){
				//console.log(f+": callback is defined");
				callback_first(); //replace
				//console.log(f+": returned from callback_first");
			//}, 0);
		}else{
			//console.log(f+": callback_first is undefined");
		}
		if(typeof timeout_second == 'function'){
			defer(timeout_second);
		}
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function insertAfterElement(insert_me, after_me){
	const f = "insertAfter()";
	try{
		if(!isset(after_me)){
			return error(f, "Reference node is undfined");
		}
		after_me.parentNode.insertBefore(insert_me, after_me.nextElementSibling);
	}catch(x){
		error(f+" exception: \""+x.toString()+"\"");
	}
}

function getImmediateChildElementByClassName(element, className, assert=true){
	const f = "getImmediateChildElementByClassName()";
	try{
		let ret = null, found;
		function recurse(element, className, found){
			for (let i = 0; !found && i < element.childNodes.length; i++){
				const f = "recurse(~, ".concat(className).concat(", ~)");
				let child = element.childNodes[i];
				let classNames = child.className != undefined? child.className.split(" ") : [];
				for(let j = 0; j < classNames.length; j++) {
					if (classNames[j] == className){
						//console.log(f+": className match at index ".concat(j));
						found = true;
						//return child;
						ret = element.childNodes[i];
						break;
					}
				}
				if(found){
					//console.log(f+": match found");
					break;
				}
				//return 
				recurse(element.childNodes[i], className, found);
			}
		}
		//return 
		recurse(element, className, false);
		if(ret == null){
			if(assert){
				console.error(f+": this should never get called in error");
				console.trace();
				//console.log(f+": element not found");
				return null;
			}
		}
		return ret;
	}catch(x){
		error(f,x);
	}
}

function revealHiddenElement(element, callback, opacity){
	const f = "revealHiddenElement()";
	try{
		if(typeof opacity == 'undefined' || opacity == null || opacity == "") {
			//console.log(f+": opacity was initially undefined");
			element.style['opacity'] = 1;
		}else{
			//console.log(f+": opacity is about to be set to "+opacity);
			element.style['opacity'] = opacity;
		}
		//console.log(f+": successfully transitioned between two opaque objects");
		if(typeof callback == 'function'){
			//console.log(f+": callback defined");
			setTimeout(function(){
				const f = "revealHiddenElement().setTimeout()";
				//console.log(f+": about to invoke callback");
				callback();
				element.dispatchEvent(new Event('reveal_done'));
				//console.log(f+": returned from callback");
			}, 0);
		}else{
			//console.trace();
			//console.log(f+": callback is undefined");
		}
		//console.log(f+": returning normally");
	}catch(x){
		error(f, x);
	}
}

function error(f0, x){
	const f = "error(".concat(f0).concat(", ~)");
	try{
		let string = typeof x === typeof "string" ? x : x.toString();
		let err = f0.concat(" exception: \"").concat(string).concat("\"");
		console.error(err);
		console.trace();
		window.alert(err);
	}catch(x){
		console.trace();
		console.error(f+": fatal exception printing exception.toString()");
	}
}

function getCurrentUserKey(){
	const f = "getCurrentUserKey()";
	try{
		return document.getElementById("user_key").value;
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
		console.trace();
	}
}

function extendEventListener(target, event_name, extend){//temporary event listener that removes itself once it fires
	const f = "extendEventListener()";
	try{
		//console.log(f+": overloading infobox cancel input behavior");
		//let info_box_check = document.getElementById("info_box_check");
		let reset_cancel = function(func){
			const f = "reset_cancel()";
			try{
				//console.log(f+": removing event listener");
				target.removeEventListener(event_name, func);
				//InfoBoxElement.resetInfoBox();
			}catch(x){
				error(f, x);
			}
		}
		let intercept_event = function(event){
			const f = "intercept_event()";
			try{
				event.preventDefault();
				//console.log(f+": calling injected function and removing event listener");
				extend();
				reset_cancel(intercept_event);
			}catch(x){
				error(f, x);
			}
		}
		let cleanup = function(event){
			const f = "cleanup()";
			try{
				target.removeEventListener(event_name, intercept_event);
				target.removeEventListener("cleanup", cleanup);
			}catch(x){
				error(f, x);
			}
		}
		target.addEventListener(event_name, intercept_event);
		target.addEventListener("cleanup", cleanup);
		//console.log(f+": returning normally");
	}catch(x){
		error(f, x);
	}
}

function iteratorToObject(it){
	let obj = {};
	for(const [key, value] of it){
		obj[key] = value;
	}
	return obj;
}

function getEffectiveDisplay(root){
	let f = "getEffectiveDisplay()";
	try{
		let node = root;
		let display = window.getComputedStyle(root).getPropertyValue("display");
		if(display == "none"){
			return display;
		}
		let i = 0;
		while(isset(node.parentNode) && node.parentNode instanceof Element){
			node = node.parentNode;
			if(!node instanceof Element){
				window.alert(f.concat(": after ").concat(i).concat(" iterations, node is not an Element"));
			}
			let p = display = window.getComputedStyle(node).getPropertyValue("display");
			if(display == "none"){
				return display;
			}
			i++;
		}
		if(display == null){
			return "static";
		}
		return display;
	}catch(x){
		error(f, x);
	}
}

function replaceNode(newNode, existingNode, effect=EFFECT_NONE, callback_success){
	const f = "replaceNode()";
	try{
		let print = false; //existingNode.id === "login_replace";
		if(print){
			window.alert(f.concat(": entered, existing node's ID is ").concat(existingNode.id));
		}
		if(newNode.ownerDocument.title !== document.title){
			document.adoptNode(newNode);
		}
		if(!isset(existingNode)){
			return error(f, "Existing node is undefined");
		}else if(!isset(existingNode.parentNode)){
			return error(f, "Existing node lacks a parent node");
		}
		if(isset(existingNode.id)){
			if(print){
				window.alert(("Existing node's name is \"").concat(existingNode.id).concat("\""));
			}
			existingNode.id = null;
		}
		if(print){
			console.log(f+": existing node is defined, as is its parent node on line 609");
		}
		/*if(existingNode.parentNode.tag === "head"){
			if(print){
				console.log(f+": parent node is the head element");
			}
			effect = EFFECT_NONE;
		}else if(print){
			console.log(f+": parent node is something other than the head");
		}*/
		
		if(
			existingNode.hasAttribute("hidden") || existingNode.classList.contains("hidden")
			|| newNode.hasAttribute("hidden") || newNode.classList.contains("hidden")
		){
			effect = EFFECT_NONE;
		}
		
		switch(effect){
			case EFFECT_FADE:
				if(print){
					console.log(f+": about fade out and back in with new element");
				}
				if(!isset(newNode.style) || !newNode.style instanceof CSSStyleDeclaration){
					console.error(newNode);
					return error(f, "newNode.style is not an instanceof CSSStyleDeclaration");
				}
				if(!hasStyleProperty(existingNode, "transition")){
					existingNode.style['transition'] = "opacity 0.5s";
				}
				if(!hasStyleProperty(newNode, "transition")){
					newNode.style['transition'] = "opacity 0.5s";
				}
				if(print){
					window.alert("About to set new node opacity");
				}
				newNode.style['opacity'] = 0;
				if(print){
					window.alert("Set new node opacity");
				}
				let computed;
				let display = getEffectiveDisplay(existingNode);
				if(print){
					window.alert(f.concat(": got effective display property \"").concat(display).concat("\""));
				}
				if(display == "none"){
					if(print){
						window.alert(f.concat(": Display none"));
					}
					computed = 0;
				}else{
					if(print){
						window.alert(f.concat(": Display is \"").concat(display).concat("\""));
					}
					computed = window.getComputedStyle(existingNode).getPropertyValue('opacity');
				}
				let old_opacity;
				if(computed === ""){
					console.error(f+": style/opacity is \""+existingNode.style.opacity+"\" for element with id \""+existingNode.id+"\"; don't call this on elements without defined opacity");
					return;
					//old_opacity = 1;
				}else if(computed == 0){
					if(print){
						console.log(f+": computed opacity is 0");
					}
					old_opacity = 1;
					//newNode.style['opacity'] = 0;
				}else{
					if(print){
						console.log(f.concat(": computed opacity is ").concat(computed));
						window.alert(f.concat(": computed display is ").concat(display));
					}
					old_opacity = 1;
					//newNode.style['opacity'] = 0;
				}
				//let parent = existingNode.parentNode;
				let replace = function(){ 
					const f = "replace";
					try{
						if(isset(newNode.parentNode)){
							console.log(newNode);
							return error(f, "New node should not have a parent node on line 447");
						}else if(!isset(existingNode.parentNode)){
							return error(f, "existing node's parent node is undefined on line 384");
						}
						//console.log(f.concat(": new node ID is \"").concat(newNode.id).concat("\""));
						//console.log(f.concat("existing node's ID is \"").concat(existingNode.id).concat("\""));
						existingNode.parentNode.replaceChild(newNode, existingNode);
						if(newNode.hasAttribute("temp_id")){
							let temp_id = newNode.getAttribute("temp_id");
							newNode.id = temp_id;
							newNode.removeAttribute("temp_id");
						}else if(print){
							console.log(f+": new node does not have a temporary ID attribute");
						}
						existingNode = null;
						if(print){
							window.alert("Replaced node");
						}
					}catch(x){
						return error(f, x);
					}
				}
				if(print){
					console.log(f+": element opacity is "+old_opacity);
				}
				if(computed == 0){
					if(print){
						console.log(f+": element is already transparent 676");
					}
					if(!isset(existingNode.parentNode)){
						return error(f, "existing node's parent node is undefined on line 679");
					}
					if(print){
						window.alert("About to call replace");
					}
					replace();
					newNode.style['opacity'] = null;
					if(typeof callback_success == 'function'){
						//console.log(f+": Success callback is defined");
						setTimeout(function(){
							const f = "replaceNode().setTimeout()";
							console.log(f.concat(": Success callback entered"));
							callback_success();
							console.log(f.concat(": returned from success callback"));
						}, 0);
					}else if(print){
						console.log(f+": success callback is undefined");
					}
					return true;
				}
				let listener = function(event){
					const f = "replaceNode.ontransitionend";
					try {
						//console.log(f.concat(": Entered"));
						let reveal = function(event){ 
							const f = "reveal";
							//console.log(f+": Entered");
							if(print){
								if(!isset(callback_success)){
									let err = "no, the callabck does not exist";
									console.log(f.concat(": ").concat(err));
									//return error(f, err);
								}else if(typeof callback_success == 'function'){
									console.log(f+": yes, the callback exists, and it's a function");
								}else{
									console.log(f+": no, the callabck is not a function");
								}
								console.log(f+": about to reveal hidden element");
							}
							revealHiddenElement(newNode, callback_success, old_opacity);
							if(print){
								console.log(f+": revealed hidden element");
							}
						}
						if(isset(newNode.parentNode)){
							console.log(newNode);
							return error(f, "New node should not have a parent node on line 447");
						}else if(!isset(existingNode.parentNode)){
							console.log(existingNode);
							return error(f, "existing node's parent node is undefined on line 450");
						}
						if(print){
							console.log(f.concat(": about to call transitionEndHandler()"));
						}
						transitionEndHandler(existingNode, replace, reveal, listener);
						if(print){
							console.log(f.concat(": returned from calling transitionEndHandler"));
						}
					}catch(x){
						console.error(f+" exception calling transition end handler: \""+x.toString()+"\"");
						callback_error();
					}
				}
				existingNode.addEventListener("transitionend", listener, false);
				if(print){
					console.log(f+": added transition end listener");
				}
				existingNode.style['opacity'] = 0;
				if(print){
					console.log(f+": made the thing transparent");
				}
				break;
			case EFFECT_NONE:
			default:
				if(print){
					console.log(f.concat(": default case \"").concat(effect).concat("\""));
				}
				existingNode.parentNode.replaceChild(newNode, existingNode);
				if(typeof callback == 'function'){
					callback();
				}
				break;
		}
	}catch(x){
		return error(f, x);
	}
}

function hasStyleProperty(element, property_name){
	let style = element.getAttribute("style");
	return style && style.indexOf(property_name) != -1
}

function replaceInnerHTML(element, html, callback_success, callback_error){
	const f = "replaceInnerHTML()";
	try{
		let print = element.id === "login_replace";
		if(print){
			console.log(f+": Entered");
		}
		if(!isset(callback_success)){
			let err = "upon entry, no, the callabck does not exist";
			if(print){
				console.log(f.concat(": ").concat(err));
			}
		}else if(typeof callback_success == 'function'){
			if(print){
				console.log(f+": upon entry, yes, the callback exists, and it's a function");
			}
		}else if(print){
			console.trace();
			console.log(f+": upon entry, no, the callabck is not a function");
		}
		if(element == null){
			let err = "element is undefined";
			return error(f, err);
		}
		if(!(hasStyleProperty(element, "transition"))){
			element.style['transition'] = "opacity 0.5s";
		}
		const computed = window.getComputedStyle(element).getPropertyValue('opacity');
		let old_opacity;
		if(computed == ""){
			console.error(f+": style/opacity is \""+element.style.opacity+"\" for element with id \""+element.id+"\"; don't call this on elements without defined opacity and opacity transition");
			old_opacity = 1;
			return;
		}else if(computed == 0){
			if(print){
				console.log(f+": computed opacity is 0");
			}
			old_opacity = 1;
		}else{
			old_opacity = 1;
		}
		let replace = function(){ 
			const f = "replace";
			if(print){
				console.log(f.concat(": Entered; about to replace inner HTML"));
			}
			if(typeof(html) == "string"){
				if(print){
					console.log(f.concat(": HTML \"").concat(html).concat("\" is a string"));
				}
				element.innerHTML = html;
			}else if(typeof(html) == "object"){
				if(print){
					console.log(f+": HTML is an object");
					console.log(html);
					console.log(f+": logged html object");
				}
				if(isElement(html) || html instanceof DocumentFragment){
					if(print){
						console.log(f+": html is an element or document fragment")
					}
					element.innerHTML = null;
					if(print){
						console.log(f.concat(": About to append the following:"));
						console.log(html);
					}
					if(html instanceof DocumentFragment && html.innerHTML){
						element.innerHTML = html.innerHTML;
					}else{
						element.appendChild(html);
					}
				}else if(isset(html.innerHTML)){
					if(print){
						console.log(f.concat(": html has innerHTML defined as string \"").concat(html.innerHTML).concat("\""));
					}
					element.innerHTML = html.innerHTML;
				}else if(isset(html.childNodes)){
					element.innerHTML = null;
					for(let i = 0; i < html.childNodes.length; i++){
						let child_array = html.childNodes[i];
						if(print){
							console.log(f.concat(": about to log array for child at index ").concat(i));
							console.log(child_array);
							console.log(f+": logged child array");
						}
						if(typeof(child_array) == "string"){
							if(print){
								console.log(f+": child is just a string");
							}
							element.innerHTML += child_array;
						}else{
							let node = hydrateElement(child_array, null);
							if(!isset(node)){
								return error(f, "getElementFromArray returned null");
							}else if(print){
								console.log(f+": about to log node returned by getElementFromArray");
								console.log(node);
							}
							if(print){
								console.log(f+": about to append child node");
							}
							element.appendChild(node);
							if(print){
								console.log(f+": appended child node");
							}
						}
					}
				}else if(print){
					console.log(f+": html has neither innerHTML or childNodes")
				}
			}else{
				return error(f, "Element is neither string nor object");
			}
			if(print){
				console.log(f+": replaced inner HTML");
			}
		}
		if(print){
			console.log(f+": element opacity is "+old_opacity);
		}
		if(computed == 0){
			if(print){
				console.log(f+": element is already transparent 882");
			}
			replace();
			if(typeof callback_success == 'function'){
				if(print){
					console.log(f+": Success callback is defined");
				}
				setTimeout(function(){
					const f = "replaceInnerHTML().setTimeout()";
					if(print){
						console.log(f.concat(": Success callback entered"));
					}
					callback_success();
					if(print){
						console.log(f.concat(": returned from success callback"));
					}
				}, 0);
			}else if(print){
				console.log(f+": success callback is undefined");
			}
			return true;
		}
		let listener = function(event){
			const f = "replaceInnerHTML.ontransitionend";
			try {
				if(print){
					console.log(f.concat(": Entered"));
				}
				let reveal = function(event){ 
					const f = "reveal";
					if(print){
						console.log(f+": Entered");
					}
					if(!isset(callback_success)){
						if(print){
							let err = "no, the callabck does not exist";
							console.log(f.concat(": ").concat(err));
							
						}
					}else if(typeof callback_success == 'function'){
						if(print){
							console.log(f+": yes, the callback exists, and it's a function");
						}
					}else if(print){
						console.log(f+": no, the callabck is not a function");
					}
					if(print){
						console.log(f+": about to reveal hidden element");
					}
					revealHiddenElement(element, callback_success, old_opacity);
					if(print){
						console.log(f+": revealed hidden element");
					}
				};
				if(print){
					console.log(f.concat(": about to call transitionEndHandler()"));
				}
				transitionEndHandler(element, replace, reveal, listener);
				if(print){
					console.log(f.concat(": returned from calling transitionEndHandler"));
				}
			}catch(x){
				console.error(f+" exception calling transition end handler: \""+x.toString()+"\"");
				callback_error();
			}
		}
		element.addEventListener("transitionend", listener, false);
		if(print){
			console.log(f+": added transition end listener");
		}
		element.style['opacity'] = 0;
		if(print){
			console.log(f+": made the thing transparent");
		}
		return true;
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
		return;
	}
}

function isMobileSafari(){
	return window.navigator.userAgent.match(/iP(ad|hone|od).+Version\/[\d\.]+.*Safari/i) ? true : false;
}

function isUrl(url){
	try{
		let a	= document.createElement('a');
		a.href = url;
		return (a.host && a.host != window.location.host);
	}catch(x){
		console.error(f+" exception: "+x.toString());
		return false;
	}
}

/*function setFormSubmitHandler(form, callback_success, callback_error){
	return AjaxForm.setFormSubmitHandler(form, callback_success, callback_error);
}*/

function removeElementById(id, callback) {
	const f = "removeElementById(".concat(id).concat(")");
	try{
		let e = document.getElementById(id);
		if(e == null){
			//console.trace();
			console.error(f+": element does not exist");
			return;
		}
		e.parentNode.removeChild(e);
		if(typeof callback == "function"){
			callback();
		}
		console.log(f+": removed element");
	}catch(x){
		return error(f, x);
	}
}

function getInputCheckedStatus(id){
	const f = "getInputCheckedStatus()";
	try{
		let priunt = true;
		console.log(f.concat(": entered; ID is \"").concat(id).concat("\""));
		if(!elementExists(id)){
			if(print){
				console.log(f+": element does not exist");
			}
			return null;
		}
		let u = document.getElementById(id);
		if(u.checked === true){
			if(print){
				console.log(f+": aye, 'tis indeed checked it is");
			}
			return true;
		}else{
			if(print){
				console.log(f+": nay, 'tisn't checked");
			}
			return false;
		}
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
		return null;
	}
}

function shrinkElementById(id, callback){
	const f = "shrinkElementById(".concat(id).concat(")");
	try{
		//console.log(f+": placeholder: set its max-height to 0, then remove it");
		let element = document.getElementById(id);
		element.style['transition'] = "all 0.5s";
		element.style['max-height'] = "".concat(element.offsetHeight).concat("px");
		let listener = function(event){
			let f0 = f.concat(".ontransitionend");
			try{
				console.log(f0.concat(": Entered"));
				let do_first = function(event){ 
					const f = "do_first";
					//console.log(f.concat(": Do this thing first");
					console.log(f+": This is the thing that happens first when shrinking a faded element to oblivion");
				}
				let timeout_second = function(event){ 
					const f = "timeout_second";
					console.log(f+": Do this thing second");
					removeElementById(id, callback);
				}
				console.log(f0.concat(": about to call transitionEndHandler()"));
				transitionEndHandler(element, do_first, timeout_second, listener);
				console.log(f0.concat(": returned from calling transitionEndHandler"));
			}catch(x){
				console.error(f+" exception calling transition end handler: \""+x.toString()+"\"");
				callback_error();
			}
		}
		element.addEventListener("transitionend", listener, false);
		element.style['max-height'] = element.style['padding-top'] = element.style['padding-bottom'] = element.style['margin-top'] = element.style['margin-bottom'] = '0px';
		//console.log(f+": set max-height to 0");
	}catch(x){
		return error(f, x);
	}
}

function fadeElementById(id, callback){
	const f = "fadeElementById(".concat(id).concat(")");
	try{
		if(!isset(id)){
			return error(f, "Fuck off with your undefined element ID");
		}
		//console.log(f+": placeholder: set element transition to all 0.5s, fade element opacity to zero, set its max height to current height, set its max-height to 0, then remove it");
		let element = document.getElementById(id);
		element.style['transition'] = "all 0.5s";
		element.style['opacity'] = "1";
		let listener = function(event){
			let f0 = f.concat(".ontransitionend");
			try{
				//console.log(f0.concat(": Entered"));
				let do_first = function(event){ 
					let f1 = "do_first";
					console.log(f1.concat(": Do this thing first"));
					//element.style['max-height'] = element.offsetHeight;
				}
				let timeout_second = function(event){ 
					const f = "timeout_second";
					console.log(f+": This function gets called second when fading an element out of existence");
					shrinkElementById(id, callback);
				}
				console.log(f0.concat(": about to call transitionEndHandler()"));
				transitionEndHandler(element, do_first, timeout_second, listener);
				console.log(f0.concat(": returned from calling transitionEndHandler"));
			}catch(x){
				console.error(f+" exception calling transition end handler: \""+x.toString()+"\"");
			}
		}
		element.addEventListener("transitionend", listener, false);
		element.style['opacity'] = '0';
		//console.log(f+": set opacity to 0");
	}catch(x){
		return error(f, x);
	}
}

function toggleInputWithId(id){
	const f = "toggleInputWithId(".concat(id).concat(")");
	try{
		let check = document.getElementById(id);
		if(!isset(check)){
			let err = f.concat(": checkbox/radio button with ID \"").concat(id).concat("\" does not exist");
			console.error(err);
			console.trace();
			return;
		}else if(check.checked == true){
			//console.log(f+": already checked -- unchecking it now");
			check.checked = false;
		}else{
			//console.log(f+": unchecked -- checking it now");
			check.checked = true;
		}
	}catch(x){
		return error(f, x);
	}
}

function checkInputWithId(id, callback){
	const f = "checkInputWithId(".concat(id).concat(")");
	try{
		let radio = document.getElementById(id);
		if(!isset(radio)){
			let err = f.concat(": radio button with ID \"").concat(id).concat("\" does not exist");
			console.error(err);
			console.trace();
			//console.log(err);
			return;
		}else if(radio.checked == true){
			//do nothing, already open
			//console.log(f+": button is already checked");
		}else{
			//console.log(f+": checked a radio button");
			radio.checked = true;
		}
		if(typeof(callback) == "function"){
			callback();
		}
	}catch(x){
		return error(f, x);
	}
}

function fetch_xhr(method, action, body_unserialized, callback_success, callback_error=null){
	const f = "fetch_xhr()";
	try{
		let print = false;
		//capitalize HTTP request method
			switch(method){
				case "GET":
				case "POST":
					break;
				case "get":
					method = "GET";
					break;
				case "post":
					method = "POST";
					break;
				default:
					return error(f, "Invalid request method \"".concat(method).concat("\""));
			}
		//if XMLHttpRequest exists, use it
		if(typeof XMLHttpRequest === 'function' || typeof	ActiveXObject === 'function'){
			let xhr;
			if(typeof(callback_success) !== "function"){
				console.error(callback_success);
				return error(f, "callback_success is not a function");
			}else if(typeof XMLHttpRequest === 'function'){
				xhr = new XMLHttpRequest();
			}else if(typeof	ActiveXObject === 'function'){
				xhr = new ActiveXObject("Microsoft.XMLHttp");
			}
			//add event listener for success callback
				xhr.addEventListener("load", function(event){
					const f = "load event listener";
					try{
						let rt = event.target.responseText;
						if(print){
							console.trace(f+": about to log response text");
							console.log(rt);
						}
						if(rt == null || rt == ""){
							console.error(f+": response text is null/empty string");
							let type = typeof callback_error;
							if(type == "function"){
								if(print){
									console.log(f+": error callback is a function");
								}
								callback_error();
							}else if(type == "string"){
								if(print){
									console.log(f+": error callback is a string, better hope it's also a function");
								}
								callback_error();
							}else{
								if(print){
									console.log(f+": error callback is something else, about to log");
									console.log(callback_error);
								}
								callback_error();
								if(print){
									console.log(f+": invoked error callback");
								}
							}
							return; 
						}else if(!isJson(rt)){
							console.error(f+": response text \""+rt+"\" is not valid JSON");
							/*try{
								JSON.parse(rt);
							}catch(x){
								return error(f, x);
							}*/
							callback_error(rt);
							return;
						}
						//let parsed = JSON.parse(rt);
						let type = typeof callback_success;
						if(type !== "function"){
							console.error(callback_success);
							return error(f, "callback_success is a ".concat(type));
						}else if(print){
							console.log(f+": XHR loaded successfully; about to create new ResponseText");
						}
						let response = new ResponseText(rt);
						if(print){
							console.log(f+": about to invoke callback \""+callback_success+"\"");
						}
						callback_success(response);
						if(print){
							console.log(f+": returned from invoking callback");
						}
					}catch(x){
						error(f, x);
						return callback_error();
					}
				});
			//add event listener for error callback
				xhr.addEventListener("error",
					function(event){
						const f = "error event listener";
						console.error(f+" XHR error; invoking callback");
						console.trace();
						if(typeof(callback_error) == "string"){
							if(print){
								console.log(f+": error callback is a string, better hope it's also a function");
							}
							callback_error();
						}else{
							let rt = event.target.responseText;
							console.error(f+": XHR failed; about to create a new ResponseText");
							let response = new ResponseText(rt);
							callback_error(response);
						}
						if(print){
							console.log(f+": callback invoked; returning in disgrace");
						}
					}
				);
			//forms using the GET request method need to have the parameters appended to the URL
				if(method === "GET"){
					if(body_unserialized instanceof FormData){
						let replacement = {};
						for(let key of body_unserialized.keys()){
							let value = body_unserialized.get(key);
							if(value == null || value == ""){
								console.log(f.concat(": skipping over empty value for key \"").concat(key).concat("\""));
								continue;
							}
							console.log(key.concat(", ").concat(value));
							replacement[key] = value;
						}
						body_unserialized = replacement;
					}
					if(!empty(body_unserialized)){
						let query = http_build_query(body_unserialized);
						console.log(query);
						action = action.concat('?').concat(query);
						console.log(action);
					}
					body_unserialized = null;
				}
			//send the XHR
				xhr.open(method, action, true);
				xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
				if(body_unserialized == null){
					if(print){
						console.log(f+": There is no body. I hope you know what you're doing.");
					}
				}else{
					let type = typeof body_unserialized;
					if(type == 'string'){
						if(print){
							console.log(f+": body_unserialized is a string; about to set request header");
						}
						xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
					}else if(type == 'object'){
						if(body_unserialized.constructor.name == 'FormData'){
							if(print){
								console.log(f+": body_unserialized is probably a FormData; skipping content-type header");
							}
						}else{
							body_unserialized = http_build_query(body_unserialized);
							xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
						}
					}else{
						console.error(f+": body_unserialized is type \""+type+"\"");
						console.trace();
						return;
					}
				}
				if(print){
					console.log(f+": about to send XHR");
				}
				xhr.send(body_unserialized);
				if(print){
					console.log(f+": sent XHR");
				}
			return;
		}
		//XHR doesn't exist (e.g. inside service worker), we are going to fetch instead
		if(body_unserialized instanceof FormData){
			console.log(f+": body is an instanceof FormData");
			//body_unserialized.append('js', 1);
			body_unserialized.append('sw', 1);
			let body = body_unserialized;
			//console.log(body);
			//let content_type = "multipart/form-data; charset=utf-8; boundary=".concat(Math.random().toString().substr(2));
			/*for(let key of body_unserialized.entries()){
				console.log(f.concat(": appending value \"").concat(key[1]).concat("\" to key \"").concat(key[0]).concat("\""));
				body[key[0]] = key[1];	//console.log(key[0] + ', ' + key[1]);
			}
			let content_type = 'application/x-www-form-urlencoded';*/
			if(print){
				for(let p of body){
					console.log(p);
				}
			}
			let headers = {
				'X-Requested-With':'XMLHttpRequest'
			};
		}else{
			console.log(f+": body is not a FormData");
			//body_unserialized.js = 1;
			body_unserialized.sw = 1;
			let body = JSON.stringify(body_unserialized);
			let headers = {
				'Content-Type':'application/x-www-form-urlencoded',
				'X-Requested-With':'fetch'
			};
		}
		fetch(action, {
			method:method,
			headers:headers,
			body:body
		}).then(function(response_encoded){
			if(response_encoded == null){
				console.error(f+": response_encoded is undefined");
			}
			console.log(f+": response_encoded is not undefined");
			response_encoded.text().then(function(json){
				if(!isset(json) || json == ""){
					console.error(f+": response_encoded.json is null or empty string");
					console.trace();
					console.log(response_encoded);
					return;
				}else if(!isJson(json)){
					console.error(f+": response_encoded.json is not JSON");
					console.trace();
					console.log(json);
					return;
				}
				console.log(f+": response_encoded.json is valid JSON");
				console.log(json);
				let parsed = JSON.parse(json);
				callback_success(parsed);
			});
		}).catch(function(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			if(typeof callback_error === "function"){
				callback_error();
			}
			return;
		});
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function getRandomString(length, lex){
	const f = "getRandomString(".concat(length).concat(", ").concat(lex).concat(")");
	try{
		let string = "";
		for (let i = 0; i < length; i++){
			string = string.concat(lex[Math.floor(Math.random() * lex.length)]);
		}
		return string;
	}catch(x){
		error(f, x);
	}
}

function getRandomAlphanumericString(length){
	return getRandomString(length, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
}

function getRandomHexString(length){
	return getRandomString(length, '0123456789abcdef');
}

function insecureNumericHash(str){
	const f = "insecureNumericHash()";
	try{
		let hash = 0;
		if (str.length == 0){
			return hash;
		}
		for (let i = 0; i < str.length; i++) {
			hash = ((hash << 5) - hash) + str.charCodeAt(i);
			hash = hash & hash;
		}
		return hash;
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function SoundEffect(src){
	this.sound = document.createElement("audio");
	this.sound.src = src;
	this.sound.setAttribute("controls", "none");
	this.sound.setAttribute("preload", "auto");
	this.sound.style.display = "none";
	this.play = function(){
		this.sound.play();
	}
	this.stop = function(){
		this.sound.pause();
	}
	return this.sound;
}

function error_cb(something){
	const f = "error_cb()";
	try{
		if(isset(something)){
			//console.log(f+": received some params");
			//console.log(something);
		}
		InfoBoxElement.showInfoBox(STRING_ERROR_PROCESSING_REQUEST);
	}catch(x){
		return error(f, x);
	}
};

function isJson(s){
	try{
		JSON.parse(s);
		return true;
	}catch (e) {
		return false;
	}
}

function replacePageContent(content, callback_success, callback_error){
	const f = "replacePageContent()";
	try{
		replaceInnerHTMLById("page_content", content, callback_success, callback_error);
	}catch(x){
		return error(f, x);
	}
}

function isElementInViewport(element){//for whatever reason, this function no longer works in brave mobile
	const f = "isElementInViewport()";
	try{
		if(element == null){
			console.trace();
			InfoBoxElement.showInfoBox(f+" error: Element is null");
			return;
		}
		let rect = element.getBoundingClientRect();
		InfoBoxElement.showInfoBox(f.concat(": rectangular boundaries are ").concat(rect.top).concat(", ").concat(rect.left).concat(", ").concat(rect.bottom).concat(", ").concat(rect.right));
		return(
			rect.top >= 0 && 
			rect.left >= 0 && 
			rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && 
			rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}catch(x){
		console.error(f+": exception: \""+x.toString()+"\"");
	}
}

function isScrolledIntoView(el) {
	let rect = el.getBoundingClientRect();
	return (rect.top >= 0) && (rect.bottom <= window.innerHeight);
}

let onAppend = function(element, callback) {
	let observer = new MutationObserver(function(mutations){
		mutations.forEach(function(m){
			if(m.addedNodes.length){
				callback(m.addedNodes);
			}
		})
	})
	observer.observe(element, {childList:true});
}

function refreshTimeout(timeout, callback, duration){
	const f = "refreshTimeout()";
	try{
		clearTimeout(timeout);
		return setTimeout(callback, duration);
	}catch(x){
		return error(f, x);
	}
}

function debugTextareaValue(event, button){
	const f = "debugTextareaValue()";
	try{
		let ta1 = document.createElement("textarea");
		insertAfterElement(ta1, button);
		ta1.value = "Test value1";
		let ta2 = document.createElement("textarea");
		ta2.value = "Test value 2";
		insertAfterElement(ta2, button);
	}catch(x){
		return error(f, x);
	}
}

function resetSessionTimeoutAnimation(sw=false){
	const f = "resetSessionTimeoutAnimation()";
	try{
		let print = false;
		//let st1 = document.getElementById("session_timeout_1");
		//let st2 = document.getElementById("session_timeout_2");
		//let st0 = document.getElementById("session_timeout_overlay");
		if(print){
			console.log(f+": refreshing session timeout animation in this window");
		}
		let arr = [ "session_timeout_1", "session_timeout_2", "session_timeout_overlay"];
		for(let i = 0; i < 3; i++){
			if(!elementExists(arr[i])){
				continue;
			}
			let el = document.getElementById(arr[i]);
			el.style.animation = 'none';
			el.offsetHeight;
			el.style.animation = null;
		}
		if(sw && isset(navigator.serviceWorker.controller)){
			//tell service worker to do the same in every tab except this one
			if(print){
				console.log(f+": about to send message to service worker");
			}
			sendMessageToServiceWorker("reset_timeout");
		}
	}catch(x){
		return error(f, x);
	}
}

function functionExists(function_name){
	let i_exist = window[function_name];
	if(typeof(i_exist) !== "function"){
		return false;
	}else{
		return true;
	}
}

function controller(response){
	const f = "controller()";
	try{
		let print = false;
		let use_case;
		if(isset(response.action)){
			if(is_array(response.action)){
				for(let i = 0; i < response.action.length; i++){
					let action = response.action[i];
					use_case = getApplicationClass().getUseCaseClass(action);
					use_case.handleResponse(response);
				}
			}else{
				if(print){
					window.alert(f.concat(": Specialized use case \"").concat(response.action).concat("\""));
				}
				use_case = getApplicationClass().getUseCaseClass(response.action);
				use_case.handleResponse(response);
			}
		}else{
			if(print){
				window.alert(f.concat(": Generic use case"));
			}
			UseCase.handleResponse(response);
		}
	}catch(x){
		return error(f, x);
	}
}

function callback_generic(response){
	const f = "callback_generic()";
	try{
		switch(response.status){
			case SUCCESS:
			//case RESULT_REGISTER_SUCCESS:
			//case RESULT_EDIT_COMMENT_SUCCESS:
				if(response.hasCommands()){
					response.processCommands();
				}
				break;
			default:
				InfoBoxElement.showInfoBox(response.info);
				if(response.hasCommands()){
					response.processCommands();
				}
				break;
		}
	}catch(x){
		return error(f, x);
	}
}

function slideReticule(event, range_input, reticule_id){
	const f = "slideReticule()";
	try{
		let reticule = document.getElementById(reticule_id);
		let ratio = 100 * (range_input.value - 0.5 + (range_input.max - range_input.min)/2);
		let position = range_input.getAttribute("orientation") == "portrait" ? "top" : "left"; //let translate = range_input.getAttribute("orientation") == "portrait" ? "Y" : "X";
		reticule.style[position] = "".concat(ratio).concat("%"); //reticule.style["transform"] = "translate".concat(translate).concat("(").concat(ratio).concat("%)");
	}catch(x){
		error(f, x);
	}
}

function urlBase64ToUint8Array(base64String){
	const f = "urlBase64ToUint8Array()";
	try{
		const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
		const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
		const rawData = window.atob(base64);
		const outputArray = new Uint8Array(rawData.length);
		for (let i = 0; i < rawData.length; ++i){
			outputArray[i] = rawData.charCodeAt(i);
		}
		return outputArray;
	}catch(x){
		error(f, x);
	}
}

function isWebWorker(){
	const f = "isWebWorker()";
	try{
		if(typeof WorkerGlobalScope !== 'undefined' && self instanceof WorkerGlobalScope){
			return true;
		}
		return false;
	}catch(x){
		return console.log(f.concat(": ").concat(x));
	}
	
}

function getAntiXsrfToken(){
	const f = "getAntiXsrfToken()";
	try{
		if(!elementExists("xsrf_token")){
			return error(f, "XSRF token is undefined");
		}
		return document.getElementById("xsrf_token").value;
	}catch(x){
		return error(f, x);
	}
}

function getSecondaryHmac(action){
	const f = "getSecondaryHmac()";
	try{
		let suffix = action.replaceAll('/', '');
		let id = "secondary_hmac-".concat(suffix);
		console.log(f.concat(": element ID is \"").concat(id).concat("\""));
		if(!elementExists(id)){
			
		}
		let e = document.getElementById(id);
		return e.value;
	}catch(x){
		return error(f, x);
	}
}

function ctype_xdigit(s){
	return /^[A-F0-9]+$/i.test(s)
}

function calculateEffectivePixelHeight(element){
	return parseInt(window.getComputedStyle(element).getPropertyValue('height'), 10);
}

function bindElement(elementClass, context){
	let f = "bindElement()";
	try{
		let print = true;
		if(!isset(bindElementFunctions)){
			return error(f, "bindElementFunctions is undefined");
		}
		console.log(bindElementFunctions);
		let func = bindElementFunctions[elementClass]; //eval("bind".concat(elementClass));
		if(typeof func !== 'function'){
			return error(f, "unable to bind ".concat(elementClass));
		}else if(print){
			console.log(f.concat(": about to bind a ").concat(elementClass));
		}
		return func(context);
	}catch(x){
		return error(f, x);
	}
}

function setCookie(name, value, ttl){
	let date = new Date();
	date.setTime(date.getTime() + (ttl * 1000));
	document.cookie = name.concat("=").concat(value).concat("; expires=").concat(date.toGMTString()).concat("; path=/").concat("; domain=").concat(WEBSITE_DOMAIN);
}

function setNonAjaxJsEnabledCookie(){
	let f = "setNonAjaxJsEnabledCookie()";
	let print = false;
	if(print){
		console.log(f+": setting cookie");
	}
	setCookie("nonAjaxJsEnabled", 1, 1440);
}

function disable(element, temp_cursor=null){
	let f = "disable()";
	try{
		if(typeof element == "string"){
			element = document.getElementById(element);
			if(typeof element == "undefined"){
				return error(f, "Element is undefined");
			}
		}
		if(temp_cursor == null){
			temp_cursor = "not-allowed";
		}
		let pe = hasStyleProperty(element, "pointer-events") ? element.style['pointer-events'] : null;
		let old_cursor = hasStyleProperty(element, "cursor") ? element.style['cursor'] : null;
		element.style['pointer-events'] = 'none';
		element.style['cursor'] = temp_cursor;
		let enable = function(event, target){
			element.removeEventListener("enable", enable);
			element.style['pointer-events'] = pe;
			element.style['cursor'] = old_cursor;
		}
		element.addEventListener("enable", enable);
	}catch(x){
		error(f, x);
	}
}

function disableWidgets(){
	let f = "disableWidgets()";
	let print = true;
	for(let i of widgetLabelIds){
		if(elementExists(i)){
			disable(i);
		}else if(print){
			console.log(f.concat(": element \"").concat(i).concat("\" does not exist"));
		}
	}
}

function enableWidgets(){
	let f = "enableWidgets()";
	let print = true;
	for(let i of widgetLabelIds){
		if(elementExists(i)){
			enable(i);
		}else if(print){
			console.log(f.concat(": element \"").concat(i).concat("\" does not exist"));
		}
	}
}

function enable(element){
	let f = "enable()";
	try{
		element.dispatchEvent(new Event("enable"));
	}catch(x){
		error(f, x);
	}
}

//XXX TODO this should be a generated template function
function createPageLoadAnimationElement(){
	let f = "createPageLoadAnimationElement()";
	try{
		let c = document.createElement("div");
		c.classList.add("page_load_c");
		c.id = "page_load_c";
		let bg = document.createElement("div");
		bg.classList.add("page_load_bg");
		bg.classList.add("background_color_1");
		c.appendChild(bg);
		let loading = document.createElement("div");
		loading.classList.add('page_load_anim');
		c.appendChild(loading);
		return c;
	}catch(x){
		error(f, x);
	}
}

function loadHyperlink(event, link, delay=null){
	let f = "loadHyperlink()";
	try{
		let print = true;
		event.preventDefault();
		if(print){
			console.log(f+": entered");
		}
		if(delay == null){
			delay = 0;
		}else if(print){
			console.log(f.concat(": Delay parameter is ").concat(delay));
		}
		
		disable(link);
		
		//let loading = createPageLoadAnimationElement();
		//loading.id = "load_pg_content";
		let page_content = document.getElementById("page_content");
		if(!isset(page_content)){
			if(print){
				console.log(f.concat(": page content is undefined. User is probably hammering a multiple links"));
			}
			delay += 100;
			setTimeout(
				function(){
					loadHyperlink(event, link, delay);
				},
				delay
			);
			return;
		}
		//document.getElementById("fixed").appendChild(loading);
		//window.alert("inspect");
		let action = link.getAttribute("href"); //link.href returns the full URL
		if(print){
			console.log(f.concat(": action href attribute is \"").concat(action).concat("\""));
		}
		let params = {
			//js:1,
			pwa:1
		};
		//break down get parameters
			if(action.includes('?')){
				let splat = action.split('?');
				action = splat[0];
				for(let i in splat[1]){
					let param_splat = splat[1].split("=");
					params[param_splat[0]] = param_splat[1];
				}
			}
		let callback;
		page_content.dispatchEvent(new Event("abort"));
		//fetch content, or get it from the cache
			let fetch = true;
			let cached = link.hasAttribute("cache");
			if(cached){
				if(print){
					console.log(f.concat(": link is cacheable"));
				}
				const resource = sessionStorage.getItem(link.href);
				if(print){
					console.log("Fetching cached page ".concat(link.href));
				}
				if(resource){
					let parsed = JSON.parse(resource);
					if(print){
						console.log(f.concat(": cache hit"));
						console.log(parsed);
					}
					if(typeof parsed.time == "undefined"){
						error(f, ": cache entry does not have a timestamp");
					}else if(!expired(parsed.time)){
						if(print){
							console.log(f.concat(": cached resource with timestamp ").concat(parsed.time).concat(" has not expired"));
						}
						fetch = false;
						let commands;
						if(isset(parsed.commands)){
							if(print){
								console.log(f.concat(": cached resource is a response"));
							}
							commands = parsed.commands;
							commands.push({
								command:"pushState",
								uri:link.href
							});
						}else if(isset(parsed.innerHTML)){
							if(print){
								console.log(f.concat(": cached resource is a chunk of html, about to fabricate commands"));
							}
							commands = [];
							if(elementExists("session_timeout_overlay")){
								commands.push({
									command:"setAttributes",
									id:"session_timeout_2",
									attributes:{
										href:link.href
									}
								});
							}
							commands.push(
								{
									command:"update",
									elements:{
										page_content:{
											attributes:{
												id:"page_content",
												"class":"page_content background_color_3"
											},
											innerHTML:parsed.innerHTML,
											tag:"div",
											inner:1
										}
									},
									subcommands:[{command:"initializeAllForms"}]
								},
								{
									command:"pushState",
									uri:link.href
								}
							);
						}else{
							return error(f, "Unable to fabricate page cache load command");
						}
						if(link.hasAttribute("callback")){
							if(print){
								let callback_name = link.getAttribute("callback");
								console.log(f.concat(": callback name is \"").concat(callback_name).concat("\""));
							}
							let callback_ref = window[link.getAttribute("callback")];
							callback = function(...params){
								callback_ref(...params);
								enable(link);
							}
						}else{
							if(print){
								console.log(f.concat(": link does not specify a callback, will use the generic one to restore cached page content"));
							}
							callback = function(response){
								response.processCommands();
								enable(link);
							};
						}
						if(typeof callback !== "function"){
							return error(f, "Callback is not a function");
						}
						let response = new ResponseText({commands:commands}, callback);
						return;
					}else if(print){
						console.log(f.concat(": cached resource for \"").concat(link.href).concat("\" with timestamp ").concat(parsed.time).concat(" has expired"));
					}
				}else if(print){
					console.log(f.concat(": cache miss"));
				}
			}else if(print){
				console.log(f.concat(": Link does not have a cache attribute"));
			}
		if(fetch){
			if(print){
				console.log(f.concat(": resource is not cached"));
			}
			let loading = createPageLoadAnimationElement();
			document.getElementById("fixed").appendChild(loading);
			//moved this from the bottom of the function
				/*page_content.addEventListener("abort", function(){
					if(isset(xhr)){
						if(print){
							console.log(f+": aborting XHR");
						}
						xhr.abort();
					}else{
						console.error(f+": error aboting XHR");
					}
					//removeElementById("page_load_c");
					enable(link);
				}, false);*/
			//moved this from up top
				if(link.hasAttribute("callback")){
					if(print){
						let callback_name = link.getAttribute("callback");
						console.log(f.concat(": callback name is \"").concat(callback_name).concat("\""));
					}
					//removeElementById("page_load_c");
					let callback_ref = window[link.getAttribute("callback")];
					callback = function(...params){
						callback(...params);
						enable(link);
					}
				}else{
					if(print){
						console.log(f.concat(": link does not specify a callback, using the default one"));
					}
					callback = function(response){
						console.log("Caching ".concat(link.href));
						let type = typeof response;
						if(type == "string"){
							console.log(f.concat(": repsonse is the string \"").concat(response).concat("\""));
							try{
								sessionStorage.setItem(link.href, response);
							}catch(x){
								if(x instanceof DOMException && x.name == "QuotaExceededError"){
									console.error(f.concat(": Quota exceeded"));
								}else{
									return error(f, x);
								}
							}
							response = new ResponseText(response);
							response.processCommands();
							window.history.pushState(null, null, link.href);
						}else if(type == "object"){
							console.log(f.concat(": response is an object"));
							/*if(response instanceof Command){
								console.log(f.concat(": Response is a command, it must have been cacned already"));
								response.execute();
							}else*/ //useless
							if(response instanceof ResponseText){
								try{
									sessionStorage.setItem(link.href, response.getBody());
								}catch(x){
									if(x instanceof DOMException && x.name == "QuotaExceededError"){
										console.error(f.concat(": Quota exceeded"));
									}else{
										return error(f, x);
									}
								}
								response.processCommands();
								window.history.pushState(null, null, link.href);
							}else{
								console.error(f.concat(": Response is not a ResponseText"));
							}
						}else{
							console.log(f.concat(": response is a ").concat(type));
						}
						enable(link);
						removeElementById("page_load_c");
					};
				}
				if(typeof callback !== "function"){
					return error(f, "Callback is not a function");
				}
			fetch_xhr(
				"GET", action, params, callback, function(){
					error_cb();
					removeElementById("page_load_c"); //page_content.removeChild(loading);
					enable(link);
				}
			);
		}else if(print){
			console.log(f.concat(": nothing to fetch, must have loaded from cache"));
		}
	}catch(x){
		return error(f, x);
	}
}

function isObject(value){
	return (typeof value === "object" || typeof value === 'function') 
	&& (value !== null) 
	&& !Array.isArray(value);
}

function printObject(obj){
	let keys = Object.keys(obj);
	for(let i = 0; i < keys.length; i++){
		let key = keys[i];
		let value = obj[key];
		if(isObject(value)){
			console.log("Object \"".concat(key).concat("\": {"));
			printObject(value)
			console.log("}");
			continue;
		}
		console.log(key.concat(" : ").concat(value));
	}
}

function handleFetchEvent(response){
	let f = "handleFetchEvent()";
	try{
		console.log(f+": entered; about to iterate through data structures");
		let i = 0;
		for(let key in response.getDataStructures()){
			let struct = response.getDataStructure(key);
			let type = struct.getDataType();
			if(type !== DATATYPE_NOTIFICATION){
				console.log(f.concat(": skipping over a data structure of type \"").concat(type).concat("\""));
				continue;
			}
			console.log(f+": about to call struct.updateElement()");
			struct.showPushNotification();
			struct.updateElement();
			i++;
		}
		if(i == 0){
			console.log(this);
			return error(f, "This object lacks any notifications");
		}
		console.log(f.concat(": done iterating through ").concat(i).concat(" data structures"));
		if(response.hasCommands()){
			console.log(f+": this response has commands -- about to process them");
			response.processCommands();
		}else{
			console.log(f+": this response lacks commands");
		}
		console.log(f+": returning normally");
		resetSessionTimeoutAnimation();
	}catch(x){
		return error(f, x);
	}
}

//yoinked from https://github.com/lodash/lodash
function isPlainObject(value){
	let f = "isPlainObject()";
	let print = false;
	if(typeof value !== 'object' || value === null || toString.call(value) != '[object Object]'){
		if(print){
			console.log(toString.call(value));
		}
		return false;
	}else if(Object.getPrototypeOf(value) === null){
		if(print){
			console.log(f+": prototype is null, returning true");
		}
		return true;
	}
	let proto = value;
	while(Object.getPrototypeOf(proto) !== null){
		proto = Object.getPrototypeOf(proto);
	}
	if(print){
		if(Object.getPrototypeOf(value) === proto){
			console.log(f+": returning true");
		}else{
			console.log(f+": returning false");
		}
	}
	return Object.getPrototypeOf(value) === proto;
}

function isElement(value){
	let f = "isElement()";
	let print = false;
	if(print){
		if(typeof value !== 'object'){
			console.log(f+": value is not an object, returning false");
			return false;
		}else if(value === null){
			console.log(f+": value is null, returning false");
			return false;
		}else if(value.nodeType !== Node.ELEMENT_NODE){
			console.log(f+": value is not an element node, returning false");
			return false;
		}else if(isPlainObject(value)){
			console.log(f+": value is a plain object, returning false");
			return false;
		}
		console.log(f+": all conditions satisfied, returning true");
		return true;
	}
	if(typeof value !== 'object' || value === null || value.nodeType !== Node.ELEMENT_NODE){
		return false;
	}
	return !isPlainObject(value);
}

/*function isElement(o){
	return (
		typeof HTMLElement === "object" ? o instanceof HTMLElement 
		: o && typeof o === "object" 
			&& o !== null 
			&& o.nodeType === 1 
			&& typeof o.nodeName === "string"
	);
}*/

function playNotificationSound(){
	let f = "playNotificationSound()";
	try{
		//console.log(f+": entered");
		let sound = document.getElementById("messenger_blip");
		if(isset(sound)){	
			sound.play();
		}
		//console.log(f+": returning normally");
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function windowResizeListener(){
	let h = window.visualViewport.height;
	let body = document.getElementById("body");
	body.style['height'] = "".concat(h).concat("px");
	//document.getElementById("menu_bar").style['background-color'] = "#".concat(genRanHex(6));
	//document.getElementById("debug_menu_bar").innerHTML = "".concat(window.visualViewport.width).concat(" ").concat(h)
	
	Menu.setViewportHeightCustomProperty();
	
	body.style['transform'] = "scale3d(0, 0, 0)";
	body.style['transform'] = null;
}

function generateSelectOptions(parent, choices){
	for(let choice of choices){
		option = document.createElement("option");
		option.value = choice.key;
		option.innerHTML = choice.value;
		parent.appendChild(option)
	}
}
