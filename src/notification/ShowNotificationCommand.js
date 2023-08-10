class ShowNotificationCommand extends Command{
	
	hasTitle(){
		return isset(this.title);
	}
	
	getTitle(){
		let f = "getTitle()";
		if(!this.hasTitle()){
			return error(f, "Title is undefined");
		}
		return this.title;
	}
	
	hasOptions(){
		return isset(this.options);
	}
	
	getOptions(){
		let f = "getOptions()";
		if(!this.hasOptions()){
			return error(f, "Options are undefined");
		}
		return this.options;
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let options = null;
			if(this.hasOptions()){
				options = this.getOptions();
			}
			let title = this.getTitle();
			NotificationData.showPushNotificationStatic(title, options);
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
