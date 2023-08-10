class GetOffsetWidthCommand extends ElementCommand{
	
	evaluate(){
		let f = this.constructor.name.concat(".evaluate()");
		try{
			let id = this.getId();
			console.log(f.concat(": ID is \"").concat(id).concat("\""));
			let element = document.getElementById(id);
			if(!isset(element)){
				return error(f, "Element with ID \"".concat(id).concat("\" does not exist"));
			}
			return element.offsetWidth;
		}catch(x){
			return error(f, x);
		}
	}
}
