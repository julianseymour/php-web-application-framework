class SetInnerHTMLCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let id = this.getId();
			let element = document.getElementById(id);
			if(!isset(element)){
				return error(f, "Cannot set innerHTML of nonexistent element \"".concat(id).concat("\""));
			}else if(print){
				console.log(f.concat(": about to replace innerHTML of element \"".concat(id).concat("\"")));
			}
			let c = this;
			let callback_success = function(){
				c.processSubcommands();
			};
			//replaceInnerHTML(element, this.getInnerHTML(), callback_success, error_cb); //
			element.innerHTML = this.getInnerHTML();
			this.processSubcommands();
		}catch(x){
			error(f, x);
		}
	}
}
