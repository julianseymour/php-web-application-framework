class InfoBoxCommand extends MultipleElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			console.log(f+": about to display info box");
			console.log(this);
			console.trace();
			let elements = this.getElements();
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
				let element = elements[key];
				if(typeof element === 'string'){
					element = document.createTextNode(element);
				}
				fragment.appendChild(element);
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
