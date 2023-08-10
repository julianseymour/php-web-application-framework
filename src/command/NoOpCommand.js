class NoOpCommand extends Command{
	
	execute(){
		this.processSubcommands();
	}
}