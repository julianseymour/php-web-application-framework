class GetOffsetHeightCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			return document.getElementById(this.getId()).offsetHeight;
		}catch(x){
			return error(f, x);
		}
	}
}
