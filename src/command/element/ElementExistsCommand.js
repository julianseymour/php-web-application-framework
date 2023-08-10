class ElementExistsCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			let id = this.getId();
			return elementExists(id);
		}catch(x){
			return error(f, x);
		}
	}
}
