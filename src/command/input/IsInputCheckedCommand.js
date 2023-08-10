class IsInputCheckedCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			let id = this.getId();
			return elementExists(id) && document.getElementById(id).checked;
		}catch(x){
			return error(f, x);
		}
	}
}
