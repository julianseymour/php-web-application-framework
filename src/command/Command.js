class Command extends ResponseProperty{
	
	constructor(obj, responseText){
		let f = "Command.constructor()";
		try{
			let print = false;
			//console.log(f.concat(": Inside Command constructor"));
			super(obj, responseText);
			if(isset(obj) && typeof obj === 'object' && isset(obj.subcommands)){
				if(print){
					console.log(f+": yes, there are subcommands to assign");
				}
				let subcommands = ResponseText.createCommands(
					obj.subcommands, 
					responseText
				);
				if(!isset(subcommands)){
					return error(f+": subcommands array returned by createCommands is somehow unset");
				}
				this.setSubcommands(subcommands);
			}else if(print){
				console.log(f+": subcommands are undefined");
			}
		}catch(x){
			error(f, x);
		}
	}
	
	static getReservedPropertyNames(){
		return ["subcommands"];
	}
	
	static createCommand(command_data, response){
		let f = "createCommand()";
		try{
			//console.log(f.concat(": about to get command class for \"").concat(command_data.command).concat("\""));
			let cmdClass = getApplicationClass().getCommandClass(command_data.command);
			return new cmdClass(command_data, response);
		}catch(x){
			return error(f, x);
		}
	}
	
	setSubcommands(subcommands){
		let f = this.constructor.name.concat(".setSubcommands()");
		try{
			let print = false;
			if(!isset(subcommands)){
				return error(f, "Received empty parameter");
			}else if(print){
				console.log(f+": assigning subcommands");
			}
			this.subcommands = subcommands;
			if(!this.hasSubcommands()){
				if(print){
					console.error(f+": about to log this.subcommands");
					console.log(this.subcommands);
				}
				if(isset(this.subcommands)){
					return error(f, "this.hasSubcommands returns something different from isset(this.subcommands)");
				}
				return error(f, "Immediately after assigning subcommands, this object doesn't have them apparently");
			}
			return this.getSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
	
	/*createSubcommands(obj){
		let f = this.constructor.name.concat(".createSubcommands()");
		try{
			if(isset(obj.subcommands)){
				console.log(f+": yes, there are subcommands to assign");
				let subcommands = ResponseText.createCommands(
					obj.subcommands, 
					this.getResponseText()
				);
				if(!isset(subcommands)){
					return error(f+": subcommands array returned by createCommands is somehow unset");
				}
				this.setSubcommands(subcommands);
			}else{
				console.log(f+": subcommands are undefined");
			}
		}catch(x){
			return error(f, x);
		}
	}*/
	
	isOptional(){
		let print = false;
		if(print){
			if(isset(this.optional)){
				console.log("This command is optional");
			}else{
				console.log("This command is not optional");
			}
			console.log(this.optional);
		}
		return isset(this.optional);
	}
	
	parse(value){
		let f = this.constructor.name.concat(".parse()");
		try{
			if(!this.hasParseType()){
				//console.log(f+": command does not specify a parse type");
				return value;
			}
			switch(this.getParseType()){
				case "bool":
				case "boolean":
					return (value == 'true');
				case "int":
					return parseInt(value);
				case "px":
				case "pixels":
					if(typeof value == 'undefined'){
						return error(f, "value is undefined");
					}
					return "".concat(value).concat("px");
				default:
					return error(f, "Invalid parse type \"".concat(command_data.parseType).concat("\""));
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	hasParseType(){
		return isset(this.parseType);
	}
	
	getParseType(){
		if(!this.hasParseType()){
			return null;
		}
		return this.parseType;
	}
	
	hasSubcommands(){
		let f = this.constructor.name.concat(".hasSubcommands()");
		try{
			return !empty(this.subcommands);
		}catch(x){
			return error(f, x);
		}
	}
	
	getSubcommands(){
		let f = this.constructor.name.concat(".getSubcommands()");
		try{
			if(!this.hasSubcommands()){
				return error(f, "Subcommands are undefined");
			}
			return this.subcommands;
		}catch(x){
			return error(f, x);
		}
	}
	
	static processSubcommandsStatic(command){
		let f = "processSubcommandsStatic()";
		try{
			if(!isset(command)){
				return error(f, "Command is undefined");
			}
			command.processSubcommands();
		}catch(x){
			error(f, x);
		}
	}
	
	processSubcommands(){
		let f = this.constructor.name.concat(".processSubcommands()");
		try{
			let print = false;
			if(!this.hasSubcommands()){
				if(print){
					console.log(f+": subcommands are undefined");
				}
				return;
			}
			let commands = this.getSubcommands();
			for(let index in commands){
				if(print){
					let err = ": Processing \"".concat(commands[index].constructor.name).concat("\" subcommand at index \"").concat(index).concat("\"");
					console.log(f.concat(err));
				}
				commands[index].execute();
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	pushSubcommand(command){
		if(!isset(this.subcommands)){
			this.subcommands = [];
		}
		return this.subcommands.push(command);
	}
}
