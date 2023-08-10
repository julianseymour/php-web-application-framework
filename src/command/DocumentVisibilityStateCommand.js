class DocumentVisibilityStateCommand extends Command{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			return document.visibilityState;
		}catch(x){
			return error(f, x);
		}
	}
}
