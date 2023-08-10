class ResponseProperty extends Basic{
	
	constructor(obj, responseText){
		super(obj, responseText);
		try{
			let f = this.constructor.name.concat(":ResponseProperty.constructor()");
			let print = false;
			if(print){
				console.log(f.concat(": Inside ResponseProperty constructor"));
			}
		//Object.assign(this, obj);
			if(typeof responseText === 'object'){
				if(print){
					console.log("Setting response text");
				}
				this.setResponseText(responseText);
			}/*else if(print){
				error(f, "ResponseText is not an object");
			}*/
		}catch(x){
			error(f, x);
		}
	}
	
	hasResponseText(){
		return isset(this.responseText) && this.responseText instanceof ResponseText;
	}
	
	setResponseText(rt){
		let f = this.constructor.name.concat("setResponseText()");
		let print = false;
		if(!rt instanceof ResponseText){
			return error(f, "Received something that is not a ResponseText");
		}else if(print){
			console.log(f.concat("Returning a response text"));
		}
		return this.responseText = rt;
	}
	
	getResponseText(){
		let f = this.constructor.name.concat(".getResponseText()");
		try{
			if(!this.hasResponseText()){
				error(f, "Response text is undefined");
				print(this);
				return null;
			}
			return this.responseText;
		}catch(x){
			return error(f, x);
		}
	}
}
