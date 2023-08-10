class Choice extends Basic{
	
	constructor(key=null, value=null, selected=false, callback=null){
		super({
			key:key,
			value:value,
			selected:selected
		}, callback);
		if(key != null){
			this.key = key;
		}
		if(value != null){
			this.value = value;
		}
		if(selected){
			this.selected = true;
		}else{
			this.selected = false;
		}
	}
}