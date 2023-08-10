class UserOwned extends DataStructure{
	
	hasUserKey(){
		return this.hasColumnValue("userKey");
	}
	
	getUserKey(){
		return this.getColumnValue("userKey");
	}
	
	setUserKey(uk){
		return this.setColumnValue("userKey", uk);
	}
	
	hasUserData(){
		return this.hasForeignDataStructure("userKey");
	}
	
	getUserData(){
		return this.getForeignDataStructure("userKey");
	}
	
	setUserData(ud){
		return this.setForeignDataStructure("userKey", ud);
	}
}
