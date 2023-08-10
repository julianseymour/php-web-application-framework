class TranslateCommand extends Command{
	
	constructor(obj, responseText){
		let f = "Command.constructor()";
		try{
			let print = false;
			//console.log(f.concat(": Inside Command constructor"));
			super(obj, responseText);
			if(isset(obj) && typeof obj === 'object' && isset(obj.substitutions)){
				let subs = [];
				for(let i in obj.substitutions){
					let sub = obj.substitutions[i];
					if(typeof sub == "object"){
						subs[i] = Command.createCommand(sub, responseText);
					}else{
						subs[i] = sub;
					}
				}
				this.substitutions = subs;
			}else if(print){
				console.log(f+": substitutions are undefined");
			}
		}catch(x){
			error(f, x);
		}
	}
	
	static getReservedPropertyNames(){
		return ["substitutions"];
	}
	
	evaluate(){
		let ret = translate(this.stringId, this.substitutions);
		this.processSubcommands();
		return ret;
	}
}