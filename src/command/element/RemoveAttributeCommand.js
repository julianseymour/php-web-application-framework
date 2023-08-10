class RemoveAttributeCommand extends AttributeCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let element = document.getElementById(this.getId());
			if(!this.hasAttributes()){
				return error(f+": attributes are undefined");
			}
			for(let attribute in this.getAttributes()){
				console.log(f.concat(": removing attribute \"").concat(attribute).concat("\""));
				element.removeAttribute(attribute);
				console.log(f.concat(": removed attribute \"").concat(attribute).concat("\""));
			}
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
