class ErrorCommand extends Command{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			console.error(this.i);
		}catch(x){
			return error(f, x);
		}
	}
}
