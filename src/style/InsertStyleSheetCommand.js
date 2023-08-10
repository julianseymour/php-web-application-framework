class InsertStyleSheetCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			//console.log(f+": about to process stylesheet insertion command");
			//console.log(command_data);
			let innerHTML = this.getInnerHTML();
			let sheet = (function(){
				let style = document.createElement("style");
				style.innerHTML = innerHTML;
				style.appendChild(document.createTextNode("")); //webkit
				document.head.appendChild(style);
				return style.sheet;
			})();
			/*if(typeof(process_subcommands) == "function"){
				process_subcommands();
			}
			break;*/
			console.log(f+": about to process subcommands");
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
