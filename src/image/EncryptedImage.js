class EncryptedImage extends EncryptedFile{
	
	setHeight(h){
		return this.setColumnValue("height", h);
	}
	
	getHeight(){
		return this.getcolumnValue("height");
	}
	
	hasHeight(){
		return this.hasColumnValue("height");
	}
	
	setWidth(w){
		return this.setColumnValue("width", w);
	}
	
	getWidth(){
		return this.getcolumnValue("width");
	}
	
	hasWidth(){
		return this.hasColumnValue("width");
	}
}
