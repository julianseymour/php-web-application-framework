class IsScrolledIntoViewCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			return isScrolledIntoView(document.getElementById(this.getId()));
		}catch(x){
			return error(f, x);
		}
	}
}
