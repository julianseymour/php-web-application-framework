class SetTextContentCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		let id = this.getId();
		let element = document.getElementById(id);
		if(!isset(element)){
			return error(f, "Cannot set textContent of nonexistent element \"".concat(id).concat("\""));
		}
		element.textContent = this.textContent;
		this.processSubcommands();
	}
}
