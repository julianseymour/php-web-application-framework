class InitializeFormCommand extends ElementCommand{
	
	hasSuccessCallback(){
		return isset(this.callback_success);
	}
	
	getSuccessCallback(){
		let f = "getSuccessCallback()";
		if(!this.hasSuccessCallback()){
			return controller;
			//return error(f, "Success callback is undefined");
		}
		return this.callback_success;
	}
	
	hasErrorCallback(){
		return isset(this.callback_error);
	}
	
	getErrorCallback(){
		let f = "getErrorCallback()";
		if(!this.hasErrorCallback()){
			return error_cb;
			//return error(f, "Error callback is undefined");
		}
		return this.callback_error;
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let print = false;
			let id = this.getId();
			if(print){
				console.log(f.concat(": about to get form \"").concat(id).concat("\""));
			}
			let form = document.getElementById(id);
			if(!isset(form)){
				return error(f, "Error initializing form: Form \"".concat(id).concat("\" does not exist"));
			}
			let callback_success = this.getSuccessCallback(); //command_data.callback_success;
			if(typeof callback_success == "undefined"){
				console.error(this);
				return error(f, "Success callback is undefined. Logged this object");
			}else if(print){
				console.log(f.concat(": callback success is \"").concat(callback_success).concat("\""));
			}
			let callback_error = this.getErrorCallback();
			AjaxForm.setFormSubmitHandler(form, callback_success, callback_error);
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}