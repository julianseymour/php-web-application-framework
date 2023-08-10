class ResetSessionTimeoutCommand extends Command{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		resetSessionTimeoutAnimation();
		this.processSubcommands();
	}
}
