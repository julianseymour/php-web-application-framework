class ElementCommand extends Command{
	
	constructor(obj, responseText){
		let f = "ElementCommand.constructor()";
		try{
			//console.log(obj);
			//window.alert(f+": entered");
			//console.log(f.concat(": inside ElementCommand constructor"));
			super(obj, responseText);
			print = false;
			if(!isset(obj)){
				return;
			}
			if(isset(obj.element)){
				this.setElement(hydrateElement(obj.element, responseText));
			}
			if(isset(obj.innerHTML)){
				if(print){
					console.log(f.concat(": printing object"));
					console.log(obj);
				}
				if(typeof obj.innerHTML == typeof "string"){
					this.innerHTML = obj.innerHTML;
				}else if(typeof obj == "object"){
					console.log(f.concat(": innerHTML is an object"));
				}
			}
		}catch(x){
			error(f, x);
		}
	}
	
	static getReservedPropertyNames(){
		return ["element"];
	}
	
	setElement(de){
		return this.element = de;
	}
	
	hasElement(){
		return isset(this.element);
	}
	
	getElement(){
		let f = this.constructor.name.concat(".getElement()");
		if(!this.hasElement()){
			return error(f, "element is undefined");
		}
		return this.element;
	}
	
	hasId(){
		return isset(this.id);
	}
	
	getId(){
		let f = this.constructor.name.concat(".getId()");
		if(!this.hasId()){
			console.log(this);
			return error(f, "ID is undefined");
		}
		return this.id;
	}
	
	hasNewId(){
		return isset(this.new_id);
	}
	
	getNewId(){
		let f = "getNewId()";
		if(!this.hasNewId()){
			return error(f, "New ID is undefined");
		}else if(this.new_id instanceof Command){
			let value = this.new_id.evaluate();
			return this.new_id.parse(value);
		}
		return this.new_id;
	}
	
	hasElement(){
		return isset(this.element);
	}
	
	getElement(){
		let f = "getElement()";
		if(!this.hasElement()){
			return error(f, "element is undefined");
		}
		return this.element;
	}
	
	hasReferenceElementId(){
		return isset(this.insert_here)
	}
	
	getReferenceElementId(){
		let f = "getReferenceElementId()";
		if(!this.hasReferenceElementId()){
			return error(f, "Insertion target ID is undefined");
		}
		return this.insert_here;
	}
	
	hasInsertWhere(){
		return isset(this.where);
	}
	
	getInsertWhere(){
		let f = "getInsertWhere()";
		if(!this.hasInsertWhere()){
			return error(f, "Insertion relativity preposition is undefined");
		}
		return this.where;
	}
	
	hasInnerHTML(){
		return isset(this.innerHTML);
	}
	
	getInnerHTML(){
		let f = "getInnerHTML()";
		if(!this.hasInnerHTML){
			return error(f, "InnerHTML is undefined");
		}else if(this.innerHTML instanceof Command){
			let value = this.innerHTML.evaluate();
			return this.innerHTML.parse(value);
		}
		return this.innerHTML;
	}
}
