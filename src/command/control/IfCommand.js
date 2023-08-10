class IfCommand extends ControlStatementCommand{
	
	constructor(obj, responseText){
		let f = "IfCommand.constructor()";
		try{
			super(obj, responseText);
			if(isset(obj.expression)){
				this.setExpression(Command.createCommand(obj.expression, responseText));
			}
			if(isset(obj.then)){
				this.setThenCommands(ResponseText.createCommands(obj.then, responseText));
			}
			if(isset(obj.elseCommands)){
				this.setElseCommands(ResponseText.createCommands(obj.elseCommands, responseText));
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	hasThenCommands(){
		return !empty(this.thenCommands);
	}
	
	getThenCommands(){
		let f = "getThenCommands()";
		if(!this.hasThenCommands()){
			return error(f, "Then commands are undefined");
		}
		return this.thenCommands;
	}
	
	setThenCommands(commands){
		return this.thenCommands = commands;
	}
	
	processThenCommands(){
		let then_commands = this.getThenCommands();
		for(let index in then_commands){
			then_commands[index].execute();
		}
		this.processSubcommands();
	}
	
	hasElseCommands(){
		return !empty(this.elseCommands);
	}
	
	getElseCommands(){
		let f = "getElseCommands()";
		if(!this.hasElseCommands()){
			return error(f, "Else commands are undefined");
		}
		return this.elseCommands;
	}
	
	setElseCommands(commands){
		return this.elseCommands = commands;
	}
	
	processElseCommands(){
		let else_commands = this.getElseCommands();
		for(let index in else_commands){
			else_commands[index].execute();
		}
		this.processSubcommands();
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			//console.log(f+": about to check a conditional media command");
			if(this.getExpression().evaluate()){
				//console.log(f+": congratulations, the conditional was satisfied");
				if(this.hasThenCommands()){
					return this.processThenCommands();
				}
			}else if(this.hasElseCommands()){
				//console.log(f+": sorry, the conditional was not satisfied; about to process else commands");
				return this.processElseCommands()
			}
			//console.log(f+": sorry, the conditional was not satisfied, and there are no else commands to process");
			return this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
