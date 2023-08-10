class CachePageContentCommand extends Command{
	
	execute(){
		cachePageContent();
		this.processSubcommands();
	}
}
