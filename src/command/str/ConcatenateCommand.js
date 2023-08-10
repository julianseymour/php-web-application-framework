class ConcatenateCommand extends Command{
	
	constructor(obj, responseText){
		super(obj, responseText);
		let f = this.constructor.name.concat(".constructor()");
		try{
			let strings = [];
			for(let s_key in obj.strings){
				let s = obj.strings[s_key];
				if(
					(typeof s === 'object' && s !== null)
					|| Array.isArray(s)
				){
					strings[s_key] = Command.createCommand(s, responseText);
				}else{
					string[s_key] = s;
				}
			}
		}catch(x){
			error(f, x);
		}
	}
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			let ret = "";
			for(let s_key in this.strings){
				s = this.strings[s_key];
				while(s instanceof Command){
					s = s.evaluate();
				}
				ret = ret.concat(s);
			}
			console.log(f.concat(": returning \"").concat(ret).concat("\""));
			return ret;
		}catch(x){
			return error(f, x);
		}
	}
}
