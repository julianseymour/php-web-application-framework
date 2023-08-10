class hCaptchaRenderCommand extends ElementCommand{
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			let print = true;
			if(print){
				console.log(f.concat(": entered"));
			}
			let type = typeof HCAPTCHA_SITE_KEY;
			if(type == 'undefined'){
				return error(f, "hCaptcha site key is undefined");
			}else if(print){
				console.log(f.concat(": hCaptcha site key is \"").concat(HCAPTCHA_SITE_KEY).concat("\""));
			}
			hcaptcha.render(this.getId(), {
				sitekey:HCAPTCHA_SITE_KEY,
				callback:function(response){
					console.log(response);
				}
			});
			this.processSubcommands();
		}catch(x){
			return error(f, x);
		}
	}
}
