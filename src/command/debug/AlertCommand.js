class AlertCommand extends Command{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			window.alert(this.i);
		}catch(x){
			return error(f, x);
		}
	}
}
