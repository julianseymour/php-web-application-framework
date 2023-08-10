class CheckInputCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let check_command = this;
			let callback = function(){
				Command.processSubcommandsStatic(check_command);
			}
			checkInputWithId(this.getId(), callback);
		}catch(x){
			return error(f, x);
		}
	}
}
