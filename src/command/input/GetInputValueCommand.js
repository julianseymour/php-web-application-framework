class GetInputValueCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			let v = document.getElementById(this.getId()).value;
			this.processSubcommands();
			return v;
		}catch(x){
			return error(f, x);
		}
	}
}
