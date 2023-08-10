class UserCorrespondence extends UserOwned{
	
	getCorrespondentKey(){
		return this.getColumnValue("correspondentKey");
	}
	
	hasCorrespondentKey(){
		return this.hasColumnValue("correspondentKey");
	}
	
	setCorrespondentKey(ck){
		return this.setColumnValue("correspondentKey", ck);
	}
	
	hasCorrespondentObject(){
		return this.hasForeignDataStructure("correspondentKey");
	}
	
	getCorrespondentObject(){
		return this.getForeignDataStructure("correspondentKey");
	}
	
	setCorrespondentObject(co){
		return this.setForeignDataStructure("correspondentKey", co);
	}
	
	hasCorrespondentAccountType(){
		return this.hasColumnValue("correspondentAccountType");
	}
	
	getCorrespondentAccountType(){
		return this.getColumnValue("correspondentAccountType");
	}
	
	setCorrespondentAccountType(ct){
		return this.setColumnValue("correspondentAccountType", ct);
	}
	
	getCorrespondentAccountTypeString(){
		return this.getCorrespondentObject().getColumnValue("accountTypeString");
	}
	
	getCorrespondentDisplayName(){
		let f = this.constructor.name.concat(".getCorrespondentDisplayName()");
		try{
			if(this.hasColumnValue("correspondentDisplayName")){
				console.log(f+": defined");
				return this.getColumnValue("correspondentDisplayName");
			}else if(this.hasCorrespondentObject()){
				console.log(f+": asking correspondent");
				return this.getCorrespondentObject().getDisplayName();
			}else if(this.hasColumnValue("correspondentName")){
				console.log(f+": returning regular name");
				return this.getColumnValue("correspondentName");
			}
			return error(f, "Correspondent display name, object, and regular name are undefined");
		}catch(x){
			return error(f, x);
		}
	}
}
