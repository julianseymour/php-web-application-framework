class PushStateCommand extends Command{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		let print = true;
		if(print){
			console.log(f.concat(": received a command to push new URL \"").concat(this.getUri()).concat("\""));
		}
		window.history.pushState(null, null, this.getUri()); //command_data.uri);
		this.processSubcommands();
	}
	
	hasURI(){
		return isset(this.uri);
	}
	
	getUri(){
		let f = "getUri()";
		if(!this.hasURI()){
			return error(f, "URI is undefined");
		}
		return this.uri;
	}
}
