class SetStylePropertiesCommand extends ElementCommand{
	
	constructor(obj, responseText){
		super(obj, responseText);
		this.createProperties(obj);
	}
	
	createProperties(obj){
		let f = "createProperties()";
		if(!isset(obj.properties)){
			return error(f, "Properties are undefined");
		}
		console.log(f+": about to log object");
		console.log(obj);
		let properties = [];
		for(let key in obj.properties){
			if(obj.properties[key] == null){
				properties[key] = null;
			}else if(typeof obj.properties[key] == "object"){
				console.log(f.concat(": property at index \"").concat(key).concat("\" is an object"));
				console.log(obj.properties[key]);
				properties[key] = Command.createCommand(obj.properties[key], this.getResponseText());
			}else{
				properties[key] = obj.properties[key];
			}
		}
		this.setProperties(properties);
	}
	
	setProperties(properties){
		return this.properties = properties;
	}
	
	hasProperties(){
		return !empty(this.properties);
	}
	
	getProperties(){
		let f = "getProperties()";
		if(!this.hasProperties()){
			return error(f, "Properties are undefined");
		}
		return this.properties;
	}
	
	evaluateProperty(key){
		let f = this.constructor.name.concat("evaluateProperty(").concat(key).concat(")");
		try{
			let properties = this.getProperties();
			let property = properties[key];
			if(property instanceof Command){
				console.log(f.concat(": Property at index \"").concat(key).concat("\" is a media command"));
				let value = property.evaluate();
				return property.parse(value);
			}
			return property;
		}catch(x){
			return error(f, x);
		}
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let id = this.getId();
			//console.log(f.concat(": about to set style attribute of element with id \"").concat(id).concat("\""));
			let element = document.getElementById(id);
			if(!isset(element)){
				//console.log(f.concat(": element with id \"").concat(id).concat("\" is undefined -- about to check whether it was flagged optional"));
				if(this.isOptional()){
					return this.processSubcommands();
				}else{
					console.error(f+": no, it wasn't");
					return error(f, "Element with ID \"".concat(id).concat("\" not found"));
				}
			}
			let properties = this.getProperties();
			for(let key in properties){
				element.style[key] = this.evaluateProperty(key); //command_data.properties[key];
			}
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
