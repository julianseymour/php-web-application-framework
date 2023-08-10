class ScrollIntoViewCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		let id = this.getId();
		let element = document.getElementById(id);
		if(!isset(element)){
			return error(f, "Cannot scroll nonexistent element \"".concat(id).concat("\" into view"));
		}
		if(isset(this.alignToTop)){
			element.scrollIntoView(true);
		}else{
			element.scrollIntoView(false);
		}
		this.processSubcommands();
	}
}
