class LogCommand extends Command{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			console.log(this.i);
		}catch(x){
			return error(f, x);
		}
	}
}
