class ReidentifyElementCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		//console.log(f+": about to reidentify element");
		document.getElementById(this.getId()).id = this.getNewId();
		this.processSubcommands();
	}
}
