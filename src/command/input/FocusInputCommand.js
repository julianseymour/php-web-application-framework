class FocusInputCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let id = this.getId();
			let e = document.getElementById(id);
			e.focus();
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}