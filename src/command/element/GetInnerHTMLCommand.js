class GetInnerHTMLCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			return document.getElementById(this.getId()).innerHTML;
		}catch(x){
			return error(f, x);
		}
	}
}
