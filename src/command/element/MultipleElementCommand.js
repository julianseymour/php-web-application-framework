class MultipleElementCommand extends ElementCommand{
	
	constructor(obj, responseText){
		super(obj, responseText);
		let f = this.constructor.name.concat(".constructor()");
		try{
			let print = false
			if(print){
				console.log(f+": entered");
				console.log(obj);
			}
			if(isset(obj.elements)){
				if(print){
					console.log(f+": now hydrating elements");
				}
				let elements = {};
				for(let key in obj.elements){
					let element
					let dehydrated = obj.elements[key];
					if(dehydrated instanceof Element){
						if(print){
							console.log(f.concat(": Element was already hydrated"));
						}
						element = dehydrated;
					}else{
						if(print){
							console.log(f.concat(": Assuming element is dehydrated"));
						}
						if(typeof dehydrated.tag == "undefined"){
							console.log(element);
							error(f, "Element tag is undefined");
							return;
						}
						element = hydrateElement(dehydrated, responseText);
						if(!isset(element)){//typeof element == "undefined"){
							console.log(dehydrated);
							error(f, ": Unable to hydrate element");
							return;
						}
					}
					if(print){
						console.log(element);
					}
					elements[key] = element;
				}
				this.setElements(elements);
			}else if(print){
				console.log(f+": no elements to hydrate");
			}
		}catch(x){
			error(f, x);
		}
	}
	
	getReservedPropertyNames(){
		return ["element", "elements"];
	}
	
	setElements(elements){
		return this.elements = elements;
	}
	
	hasElements(){
		return isset(this.elements);
	}
	
	getElements(){
		let f = this.constructor.name.concat(".getElements()");
		if(!this.hasElements()){
			return error(f, "elements array is undefined");
		}
		return this.elements;
	}
}
