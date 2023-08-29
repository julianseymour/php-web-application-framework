class DataStructure extends ResponseProperty{
	
	constructor(obj, responseText){
		super(obj, responseText);
	}
	
	hasForeignDataStructure(index){
		return this.hasColumnValue(index) 
		&& this.getResponseText().hasDataStructure(this.getColumnValue(index));
	}
	
	hasForeignDataStructureList(column_name){
		return this.hasColumnValue(column_name)
		&& !empty(this.getColumnValue(column_name));
	}
	
	getForeignDataStructure(index){
		let f = this.constructor.name.concat(".getForeignDataStructure()");
		try{
			let print = false;
			//window.alert(f+": entered");
			if(!this.hasForeignDataStructure(index)){
				return error(f, "Foreign data structure at index \"".concat(index).concat("\" does not exist"));
			}
			let key = this.getColumnValue(index);
			if(print){
				console.log(f.concat(": key at index \"").concat(index).concat("\" is \"").concat(key).concat("\""));
			}
			return this.getResponseText().getDataStructure(key);
		}catch(x){
			return error(f, x);
		}
	}
	
	setForeignDataStructure(index, ds){
		let f = this.constructor.name.concat(".setForeignDataStructure()");
		try{
			let print = false;
			if(print){
				console.trace();
				console.log(f+": entered");
			}
			let key = ds.getIdentifierValue();
			this.setColumnValue(index, key);
			return this.getResponseText().setDataStructure(key, ds);
		}catch(x){
			return error(f, x);
		}
	}
	
	getDataType(){
		return this.dataType;
	}
	
	hasColumnValue(index){
		let f = this.constructor.name.concat(".hasColumnValue(\"").concat(index).concat("\")");
		try{
			return this.hasOwnProperty(index);
		}catch(x){
			return error(f, x);
		}
	}
	
	setColumnValue(index, value){
		return this[index] = value;
	}
	
	hasIdentifierValue(){
		return this.hasColumnValue('uniqueKey');
	}
	
	getIdentifierValue(){
		return this.getColumnValue('uniqueKey');
	}
	
	setIdentifierValue(key){
		return this.setColumnValue('uniqueKey', key);
	}
	
	getColumnValue(index){
		let f = this.constructor.name.concat(".getColumnValue(").concat(index).concat(")");
		try{
			let print = false;
			if(!this.hasColumnValue(index)){
				console.log(this);
				let classname = this.constructor.name;
				let err = "Index \"".concat(index).concat("\" is undefined for ").concat(classname).concat(" of datatype \"").concat(this.dataType).concat("\" and key \"").concat(this.uniqueKey).concat("\"");
				return error(f, err);
			}
			if(print){
				let type = typeof this[index];
				console.log(f.concat(": returning ").concat(type).concat(" value \"").concat(this[index]).concat("\" for index \"").concat(index).concat("\""));
			}
			return this[index];
		}catch(x){
			return error(f, x);
		}
	}
	
	getForeignDataStructureList(column_name){
		let f = "getForeignDataStructureList";
		try{
			let list = {};
			let keys = this.getColumnValue(column_name);
			for(let i in keys){
				let foreignKey = keys[i];
				let fds = this.getResponseText().getDataStructure(foreignKey);
				list[foreignKey] = fds;
			}
			console.log(f.concat("About to log foreign data structure list"));
			console.log(list);
			return list;
		}catch(x){
			return error(f, x);
		}
	}
	
	getForeignDataStructureListMember(column_name, key){
		let f = "getForeignDataStructureListMember()";
		try{
			let print = false;
			let list = this.getForeignDataStructureList(column_name);
			if(print){
				console.log(list);
			}
			return list[key];
		}catch(x){
			return error(f, x);
		}
	}
	
	getForeignDataStructureListMemberAtOffset(column_name, offset){
		let f = "getForeignDataStructureListMemberAtOffset()";
		try{
			let print = false;
			let list = this.getForeignDataStructureList(column_name);
			if(print){
				console.log(list);
			}
			let key = Object.keys(list)[offset];
			return list[key];
		}catch(x){
			return error(f, x);
		}
	}
	
	createElement(){
		let f = "createElement()";
		try{
			console.log("About to create an element for this piece");
			console.log(this);
			if(!this.hasResponseText()){
				return error(f, "ResponseText is undefined");
			}
			return bindElement(this.elementClass, this);
		}catch(x){
			return error(f, x);
		}
	}
	
	/*isUninitialized(){
		return this.hasIdentifierValue();
	}*/
}
