class SetUniversalFormActionCommand extends Command{
	
	execute(){
		AjaxForm.setUniversalFormAction(this.action);
		this.processSubcommands();
	}
}
