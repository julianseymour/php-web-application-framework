class IntersectionObserverCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			//console.log(f+": about to assign an intersection observer");
			let id = this.getId();
			if(!elementExists(id)){
				let err = "Element with ID \"".concat(id).concat("\" is undefined");
				return error(f, err);
			}
			let element = document.getElementById(id);
			let root_id = this.getRootId();
			let root = document.getElementById(root_id);
			let threshold = this.getThreshold();
			let rootMargin = this.getRootMargin();
			//1. generate options
				let options = {
					threshold:threshold,
					root:root,
					rootMargin:rootMargin
				};
			//2. create observer. For security reasons the callback must be predeclared in an array
				let callback = this.getCallback();
				if(!isset(callback)){
					return error(f, "callback is undefined");
				}
				let callback_function = legalIntersectionObservers[callback];
				if(typeof callback_function !== 'function'){
					let err = "callback is not a function";
					return error(f, err);
				}
				let observer = new IntersectionObserver(callback_function, options);
				observer.observe(element);
			//3. add event to remove observer
				let removeMessageObserver = function(){
					observer.unobserve(element);
				}
				element.addEventListener('remove_observer', removeMessageObserver);
			console.log(f+": assigned intersection observer");
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
	
	hasCallback(){
		return isset(this.callback);
	}
	
	getCallback(){
		let f = this.constructor.name.concat(".getCallback()");
		if(!this.hasCallback()){
			return error(f, "Callback is undefined");
		}
		return this.callback;
	}
	
	hasRootId(){
		return isset(this.rootId);
	}
	
	getRootId(){
		let f = this.constructor.name.concat(".getRootId()");
		if(!this.hasRootId()){
			return error(f, "Root ID is undefined");
		}
		return this.rootId;
	}
	
	hasThreshold(){
		return isset(this.threshold);
	}
	
	getThreshold(){
		if(!this.hasThreshold()){
			return 0;
		}
		return this.threshold;
	}
	
	hasRootMargin(){
		return isset(this.rootMargin);
	}
	
	getRootMargin(){
		if(!this.hasRootMargin()){
			return 0;
		}
		return this.rootMargin;
	}
}
