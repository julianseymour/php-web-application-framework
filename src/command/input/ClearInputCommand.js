class ClearInputCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let id = this.getId();
			let clear_me = document.getElementById(id);
			if(!isset(clear_me)){
				console.error(f.concat(": element with ID \"").concat(id).concat("\" doesn't exist"));
				return;
			}
			clear_me.value = null;
			//console.log(f+": cleared input");
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
