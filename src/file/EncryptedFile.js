class EncryptedFile extends UserOwned{
	
	setWebFilePath(path){
		return this.setColumnValue("webFilePath", path);
	}
	
	getWebFilePath(){
		return this.getColumnValue("webFilePath");
	}
	
	hasWebFilePath(){
		return this.hasColumnValue("webFilePath");
	}
	
	setOriginalFilename(fn){
		return this.setColumnValue("originalFilename", fn);
	}
	
	getOriginalFilename(){
		return this.getColumnValue("originalFilename");
	}
	
	hasOriginalFilename(){
		return this.hasColumnValue("originalFilename");
	}
}
