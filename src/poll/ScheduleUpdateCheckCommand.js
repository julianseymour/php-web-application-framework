class ScheduleUpdateCheckCommand extends Command{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		ShortPollForm.scheduleUpdateCheck();
		this.processSubcommands();
	}
}