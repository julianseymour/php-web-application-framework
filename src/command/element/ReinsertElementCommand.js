class ReinsertElementCommand extends InsertElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			console.log(f+": about to reinsert a node before something");
			console.log(this);
			let id = this.getId();
			let insert_me = document.getElementById(id);
			let insert_here = this.getReferenceElementId();
			let near_me = document.getElementById(insert_here);
			let where = this.getInsertWhere();
			switch(where){
				case "after":
					insertAfterElement(insert_me, near_me);
					break;
				case "before":
					near_me.parentNode.insertBefore(insert_me, near_me);
					break;
				case "appendChild":
					near_me.appendChild(insert_me);
					break;
				default:
					let err = "Invalid reinsert preposition \"".concat(where).concat("\"");
					return error(f, err);
			}
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
