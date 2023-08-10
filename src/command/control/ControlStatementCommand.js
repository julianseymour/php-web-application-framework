class ControlStatementCommand extends Command{
	
	setExpression(expr){
		return this.expression = expr;
	}
	
	hasExpression(){
		return isset(this.expression);
	}
	
	getExpression(){
		let f = this.constructor.name.concat(".getExpression()");
		if(!this.hasExpression()){
			return error(f, "Expression is undefined");
		}
		return this.expression;
	}
}
