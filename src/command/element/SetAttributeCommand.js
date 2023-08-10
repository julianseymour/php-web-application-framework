class SetAttributeCommand extends AttributeCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let attributes = this.getAttributes();
			let id = this.getId();
			if(!elementExists(id)){
				if(this.isOptional()){
					this.processSubcommands();
					return;
				}
				let err = "Element with ID \"".concat(id).concat("\" does not exist");
				if(this.declared){
					err = err.concat(". Declared ").concat(this.declared);
				}
				return error(f, err);
			}
			let element = document.getElementById(id);
			for(let key in attributes){
				element.setAttribute(key, attributes[key]);
			}
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
