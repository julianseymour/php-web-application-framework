class InfoBoxCommand extends MultipleElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			console.log(f+": about to display info box");
			console.log(this);
			console.trace();
			//return error(f, "Not implemented");
			let elements = this.getElements(); //this.hasElement() ? hydrateElement(this.getElement(), this.getResponseText()) : this.getId(); //command_data.element);
			if(!isset(elements)){
				return error(f, "Info box contents are undefined");
			}
			console.log(elements);
			let info_command = this;
			let callback = function(){
				Command.processSubcommandsStatic(info_command);
			}
			let fragment = new DocumentFragment();
			for(let key in elements){
				fragment.appendChild(elements[key]);
			}
			InfoBoxElement.showInfoBox(
				fragment, 
				callback,
				function(data){
					//command_data.callback_error(data);
				}
			);
		}catch(x){
			return error(f, x);
		}
	}
}
