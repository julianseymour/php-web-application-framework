class GetDataStructureCommand extends Command{
	
	hasIdentifierValue(){
		return isset(this.uniqueKey);
	}
	
	getIdentifierValue(){
		let f = this.constructor.name.concat(".getIdentifierValue()");
		if(!this.hasIdentifierValue()){
			return error(f, "Key is undefined");
		}
		return this.uniqueKey;
	}
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			let rt = this.getResponseText();
			let key = this.getIdentifierValue();
			return rt.getDataStructure(key);
		}catch(x){
			return error(f, x);
		}
	}
}
