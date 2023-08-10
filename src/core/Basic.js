class Basic{
	
	getReservedPropertyNames(){
		return [];
	}
	
	constructor(obj, callback){
		let f = this.constructor.name.concat(".constructor()");
		try{
			let print = false;
			if(print){
				console.log(f.concat(": Inside LastCommon constructor"));
			}
			if(!isset(obj) || typeof obj === "undefined"){
				return;
			}else if(print){
				console.log(f+": about to log input parameter");
				console.log(obj);
			}
			//window.alert(f+": logged input parameter");
			let reserved = this.getReservedPropertyNames();
			for(let index in obj){
				if(false == array_search(index, reserved)){
					this[index] = obj[index];
				}
			}
			//console.log(f+": constructor finished");
		}catch(x){
			return error(f, x);
		}
	}
	
	assign(obj){
		for(let p in obj){
			this[p] = obj[p];
		}
	}
}
