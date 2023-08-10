class InitializeAllFormsCommand extends Command{
	
	execute(){
		console.log("Initializing all forms");
		AjaxForm.initializeAllForms();
		this.processSubcommands();
	}
}