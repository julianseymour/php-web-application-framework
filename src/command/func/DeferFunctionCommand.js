class DeferFunctionCommand extends InvokeFunctionCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		//console.log(f+": about to defer a javascript command");
		defer(invokable[this.getName()]);
		this.processSubcommands();
	}
}
