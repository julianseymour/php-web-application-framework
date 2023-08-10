class DeleteElementCommand extends Command{
	
	hasIds(){
		return isset(this.ids) && Array.isArray(this.ids) && !empty(this.ids);
	}
	
	getIds(){
		let f = this.constructor.name.concat(".getIds()");
		if(!this.hasIds()){
			return error(f, "IDs undefined");
		}
		return this.ids;
	}
	
	hasEffect(){
		return isset(this.effect);
	}
	
	getEffect(){
		return this.hasEffect() ? this.effect : EFFECT_NONE;
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let print = true;
			if(print){
				console.log(f+": about to delete an element");
			}
			let ids = this.getIds();
			if(print){
				console.log(f+": about to print IDs");
				console.log(ids);
			}
			let last_key = ids[ids.length-1];
			if(print){
				console.log(f+": about to log last key");
				console.log(last_key);
			}
			let delete_command = this;
			let delete_callback = function(){
				console.log(f+": processing subdommands");
				Command.processSubcommandsStatic(delete_command);
			}
			let effect = this.getEffect();
			for(let i in ids){
				let callback = null;
				let id = ids[i];
				if(id == last_key){
					if(print){
						console.log(f+": this is the last key -- going to process subcommands after this");
					}
					callback = delete_callback;
				}else if(print){
					console.log(f.concat(": key \"").concat(i).concat("\" is not the last key (").concat(last_key).concat("), not going to process shit"));
				}
				switch(effect){
					case EFFECT_FADE:
						fadeElementById(ids[i], callback);
						continue;
					case EFFECT_NONE:
					default:
						removeElementById(ids[i], callback);
						continue;
				}
			}
			return;
		}catch(x){
			return error(f, x);
		}
	}
}
