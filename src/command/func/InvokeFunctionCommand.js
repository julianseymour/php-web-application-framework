class InvokeFunctionCommand extends Command{
	
	constructor(obj, responseText){
		super(obj, responseText);
		let f = this.constructor.name.concat(".constructor()");
		try{
			//initialize parameters
			if(isset(obj.params)){
				console.log(f+": received object has parameters");
				let parameters = [];
				for(let index in obj.params){
					if(typeof obj.params[index] == "object"){
						console.log(f+": parameter is an object -- about to convert from media command");
						let command = Command.createCommand(obj.params[index], responseText);
						parameters[index] = command.evaluate();
					}else{
						parameters[index] = obj.params[index];
					}
				}
				this.parameters = parameters;
			}else{
				console.log(f+": parameters are undefined");
			}
		}catch(x){
			error(f, x);
		}
	}
	
	hasName(){
		return isset(this.name);
	}
	
	getName(){
		let f = "getName()";
		if(!this.hasName()){
			return error(f, "Name is undefined");
		}
		return this.name;
	}
	
	hasParameters(){
		return !empty(this.parameters);
	}
	
	getParameters(){
		let f = this.constructor.name.concat(".getParameters()");
		if(!this.hasParameters()){
			return error(f, "Parameters are undefined");
		}
		return this.parameters;
	}
}
