class NameDatum{
	
	static normalize(s){
		let f = "NameDatum.normalize()";
		try{
			return s.toLowerCase().replace(/[^a-z0-9]+/g, '_');
		}catch(x){
			return error(f, x);
		}
	}
}