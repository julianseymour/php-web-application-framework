class AttributeCommand extends ElementCommand{
	
	hasAttributes(){
		return !empty(this.attributes);
	}
	
	getAttributes(){
		const f = "getAttributes()";
		if(!this.hasAttributes()){
			return error(f, "Attributes are undefined");
		}
		return this.attributes;
	}
}
