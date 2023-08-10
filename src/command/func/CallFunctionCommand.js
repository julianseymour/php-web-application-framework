class CallFunctionCommand extends InvokeFunctionCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let params = [];
			if(this.hasParameters()){
				params = this.parameters;
			}else{
				console.log(f+": this media command does not have parameters");
			}
			let name = this.getName();
			console.log(f.concat(": function name is \"").concat(name).concat("\""));
			let callback = invokable[name];
			if(typeof callback != "function"){
				return error(f, "Function \"".concat(name).concat("\" is not a function"));
			}
			return callback.apply(null, params);
		}catch(x){
			return error(f, x);
		}
	}
	
	evaluate(){
		return this.execute();
	}
}
