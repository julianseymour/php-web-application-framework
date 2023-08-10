class BinaryExpressionCommand extends Command{
	
	constructor(obj, responseText){
		super(obj, responseText);
		this.createBinaryExpressionCommands(obj);
	}
	
	createBinaryExpressionCommands(obj){
		if(isset(obj.lhs)){
			if(typeof obj.lhs == "object"){
				this.setLeftHandSide(Command.createCommand(obj.lhs, this.getResponseText()));
			}else{
				this.setLeftHandSide(obj.lhs);
			}
		}else{
			this.setLeftHandSide(null);
		}
		if(isset(obj.rhs)){
			if(typeof obj.rhs == "object"){
				this.setRightHandSide(Command.createCommand(obj.rhs, this.getResponseText()));
			}else{
				this.setRightHandSide(obj.rhs);
			}
		}else{
			this.setRightHandSide(null);
		}
	}
	
	hasOperator(){
		return isset(this.operator);
	}
	
	getOperator(){
		const f = "getOperator()";
		if(!this.hasOperator()){
			return error(f, "Operator is undefined");
		}
		return this.operator;
	}
	
	setOperator(operator){
		return this.operator = operator;
	}
	
	getLeftHandSide(){
		return this.leftHandSide;
	}
	
	setLeftHandSide(lhs){
		return this.leftHandSide = lhs;
	}
	
	evaluateLeftHandSide(){
		if(this.leftHandSide instanceof Command){
			const lhs = this.getLeftHandSide();
			const value = lhs.evaluate();
			lhs.processSubcommands();
			return lhs.parse(value);
		}
		return this.getLeftHandSide();
	}
	
	getRightHandSide(){
		return this.rightHandSide;
	}
	
	setRightHandSide(rhs){
		return this.rightHandSide = rhs;
	}
	
	evaluateRightHandSide(){
		if(this.rightHandSide instanceof Command){
			const rhs = this.getRightHandSide();
			const value = rhs.evaluate();
			rhs.processSubcommands();
			return rhs.parse(value);
		}
		return this.getRightHandSide();
	}
	
	evaluate(){
		const f = this.constructor.name.concat(".evaluate()");
		try{
			//console.log(f+": about to process left hand side");
			//console.log(command_data.lhs);
			const lhs = this.evaluateLeftHandSide();
			//let type = typeof lhs;
			//console.log(f.concat(": Left hand side evaluated to \"").concat(lhs).concat("\" and is type \"").concat(type).concat("\""));
			//console.log(f+": about to process right hand side");
			const rhs = this.evaluateRightHandSide();
			//let type = typeof rhs;
			//console.log(f.concat(": Right hand side evaluated to \"").concat(rhs).concat("\" and is type \"").concat(type).concat("\""));
			//console.log(f.concat(": operator is \"").concat(command_data.operator).concat("\""));
			const operator = this.getOperator();
			const err = "About to evaluate \"".concat(lhs).concat(" ").concat(operator).concat(" ").concat(rhs).concat("\"");
			console.log(err);
			switch(operator){
				case '<':
					return lhs < rhs;
				case '<=':
					return lhs <= rhs;
				case '=':
				case '==':
				case '===':
					return lhs == rhs;
				case '>=':
					return lhs >= rhs;
				case '>':
					return lhs > rhs;
				case '<>':
				case '!=':
				case '!==':
					return lhs != rhs;
				case '+':
					return lhs + rhs;
				case '-':
					return lhs - rhs;
				case '*':
					return lhs * rhs;
				case '/':
					return lhs / rhs;
				case '%':
					return lhs % rhs;
				default:
					return error(f, "Invalid operator \"".concat(compare).concat("\""));
			}
		}catch(x){
			return error(f, x);
		}
	}
}
