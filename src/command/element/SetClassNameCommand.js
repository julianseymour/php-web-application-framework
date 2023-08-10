class SetClassNameCommand extends ElementCommand{

	hasClassName(){
		return isset(this.className);
	}
	
	getClassName(){
		let f = this.constructor.name.concat(".getClassName()");
		try{
			if(!this.hasClassName()){
				return error(f, "Class name is undefined");
			}else if(this.className instanceof Command){
				let value = this.className.evaluate();
				return this.className.parse(value);
			}
			return this.className;
		}catch(x){
			return error(f, x);
		}
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			//console.log(f+": about to reclassify an element");
			document.getElementById(this.getId()).className = this.getClassName();
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
