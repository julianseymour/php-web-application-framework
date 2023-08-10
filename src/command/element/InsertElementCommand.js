class InsertElementCommand extends MultipleElementCommand{
	
	constructor(obj, responseText){
		super(obj, responseText);
		this.createOnDuplicateIdCommand(obj);
	}
	
	hasOnDuplicateId(){
		return isset(this.onDuplicateId) && this.onDuplicateId instanceof Command; 
	}
	
	setOnDuplicateId(command){
		return this.onDuplicateId = command;
	}
	
	createOnDuplicateIdCommand(obj){
		if(isset(obj.onDuplicateId)){
			this.setOnDuplicateId(Command.createCommand(obj.onDuplicateId, this.getResponseText()));
		}
	}
	
	getOnDuplicateId(){
		let f = "getOnDuplicateId()";
		if(!this.hasOnDuplicateId()){
			return error(f, "On duplicate ID command is undefined");
		}
		return this.onDuplicateId;
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let insert_here = this.getReferenceElementId();
			if(!elementExists(insert_here)){
				return error(f, "Element \"".concat(insert_here).concat("\" does not exist"));
			}
			let near_me = document.getElementById(insert_here);
			let elements;
			if(this.hasElements()){
				elements = this.getElements();
			}else{
				return error(f, "Elements are undefined");
			}
			let where = this.getInsertWhere();
			switch(where){
				case "after":
					for(let key in elements){
						let insert_me = elements[key];
						insertAfterElement(insert_me, near_me);
					}
					break;
				case "before":
					console.log(f+": about to insertBefore on the reverse of the following elements:");
					console.log(elements);
					if(!isset(elements)){
						return error(f, "Elements array is undefined");
					}else if(typeof elements.reverse !== 'function'){
						elements = Object.values(elements);
					}
					for(let key in elements.reverse()){
						let insert_me = elements[key];
						near_me.parentNode.insertBefore(insert_me, near_me);
					}
					break;
				case "appendChild":
					console.log(f+": about to appendChild");
					for(let key in elements){
						let insert_me = elements[key];
						console.log(insert_me);
						near_me.appendChild(insert_me);
					}
					break;
				default:
					return error(f, "Invalid before/after string \"".concat(before_or_after).concat("\""));
			}
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
