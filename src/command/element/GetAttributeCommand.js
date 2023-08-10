class GetAttributeCommand extends AttributeCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			return document.getElementById(this.getId()).getAttribute(this.attributes[0]);
		}catch(x){
			return error(f, x);
		}
	}
}
