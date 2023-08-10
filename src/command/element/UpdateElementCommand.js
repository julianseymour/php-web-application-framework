class UpdateElementCommand extends MultipleElementCommand{
	
	hasOldId(){
		return isset(this.old_id);
	}
	
	getOldId(){
		let f = "getOldId()";
		if(!this.hasOldId()){
			return error(f, "Old ID is undefined");
		}
		return this.old_id;
	}
	
	hasEffect(){
		let f = "UpdateElementCommand.hasEffect()";
		let print = false;
		if(print){
			if(isset(this.effect)){
				console.log(f.concat(": yes, effect name is ").concat(this.effect).concat("\""));
			}else{
				console.log(f+": no, effect is undefined");
			}
		}
		return isset(this.effect);
	}
	
	getEffect(){
		return this.hasEffect() ? this.effect : EFFECT_FADE;
	}
	
	constructor(obj, responseText){
		super(obj, responseText);
		let f = this.constructor.name.concat(".constructor()");
		if(!this.hasElements()){
			return error(f, "elements are undefined");
		}
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let print = true;
			if(!this.hasElements()){
				return error(f, "elements are undefined");
			}
			if(print){
				if(this.hasSubcommands()){
					console.log(f+": before calling replaceInnerHTMLById, yes, this object has subcommands");
				}else{
					console.log(f+": before calling replaceInnerHTMLById, no, there are no subcommands");
				}
				console.log(this);
			}
			let update_command = this;
			let elements = this.getElements();
			if(!isset(elements)){
				return error(f, ": elements are undefined");
			}
			let keys = Object.keys(elements);
			let last_key = keys[keys.length-1];
			for(let old_id in elements){
				if(print){
					console.log(f.concat(": ID ").concat(old_id));
				}
				let update_callback = function(){
					if(print){
						console.log(f+": inside the callback for replacing innerHTML by ID");
					}
					if(print){
						console.log(f+": about to process subcommands");
					}
					Command.processSubcommandsStatic(update_command);
					if(print){
						console.log(f+": back from processSubcommandsStatic");
					}
				};
				let callback = old_id == last_key ? update_callback : null;
				if(!elementExists(old_id)){
					if(print){
						console.error(elements);
					}
					let err = "Element with ID \"".concat(old_id).concat("\" does not exist. ");
					if(this.debugId){
						err = err.concat("Debug ID is ").concat(this.debugId).concat(". ");
					}
					if(this.declared){
						err = err.concat("Declared ").concat(this.declared);
					}
					if(this.isOptional()){
						if(print){
							console.log(f.concat(": ").concat(err));
						}
						continue;
					}else{
						console.error(f+": this command is NOT optional");
						return error(f, err);
					}
				}else if(print){
					let err = f.concat(": about to get element with ID \"").concat(old_id).concat("\"");
					console.log(err);
				}
				let old_element = document.getElementById(old_id);
				if(!isset(old_element.parentNode)){
					return error(f, "Existing element lacks a parent node");
				}
				if(print){
					console.log(f+": existing node has a parent node");
				}
				let new_element = elements[old_id];
				if(typeof new_element == 'undefined'){
					return error(f, (": new element \"").concat(old_id).concat("\" is undefined"));
				}
				if(print){
					console.log(new_element);
					console.log(f.concat(": logged element with ID \"").concat(old_id).concat("\""));
				}
				if(this.inner){
					if(print){
						console.log(f.concat("About to call replaceInnerHTML"));
					}
					replaceInnerHTML(old_element, new_element, callback);
				}else{
					if(print){
						console.log(f.concat(": About to call replaceNode"));
					}
					old_element.id = null;
					replaceNode(new_element, old_element, this.getEffect(), callback);
				}
			}
		}catch(x){
			return error(f, x);
		}
	}
}
