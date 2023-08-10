class SetInputValueCommand extends ElementCommand{
	
	hasValue(){
		return isset(this.value);
	}
	
	getValue(){
		let f = this.constructor.name.concat(".getValue()");
		try{
			if(!this.hasValue()){
				let id = this.getId();
				return error(f, "Value is undefined for input with ID \"".concat(id).concat("\"; if you want to set it to null then use ClearInput instead"));
			}else if(this.value instanceof Command){
				let value = this.value.evaluate();
				return this.value.parse(value);
			}
			return this.value;
		}catch(x){
			return error(f, x);
		}
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			//console.log(f+": about to set an input's value attribute");
			let id = this.getId();
			let input = document.getElementById(id);
			if(!isset(input)){
				return error(f, "input with ID \"".concat(id).concat("\" is undefined"));
			}
			input.value = this.getValue();
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
