class CounterpartCorrespondence extends UserCorrespondence{
	
	hasCounterpartKey(){
		return this.hasColumnValue("counterpartKey");
	}
	
	getCounterpartKey(){
		return this.getColumnValue("counterpartKey");
	}
	
	setCounterpartKey(ck){
		return this.setColumnValue("counterpartKey", ck);
	}
	
	hasCounterpartObject(){
		return this.hasForeignDataStructure("counterpartKey");
	}
	
	getCounterpartObject(){
		return this.getForeignDataStructure("counterpartKey");
	}
	
	setCounterpartObject(cp){
		return this.setForeignDataStructure("counterpartKey", cp);
	}
}
