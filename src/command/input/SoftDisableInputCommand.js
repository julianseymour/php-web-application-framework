class SoftDisableInputCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		let id = this.getId();
		let disable_me = document.getElementById(id);
		if(!isset(disable_me)){
			error(f, "Input with ID \"".concat(id).concat("\" is undefined"));
		}else{
			disable_me.disabled = true;
		}
		this.processSubcommands();
	}
}