class Validator extends Basic{
	
	static instantValidateStatic(event, input){
		let f = "instantValidateStatic()";
		try{
			let print = false;
			if(print){
				console.log(f.concat(": Input name is \"").concat(input.name).concat("\""));
			}
			if(input.value === null || input.value === ""){
				if(input.hasAttribute("validity")){
					input.removeAttribute("validity");
				}
				if(print){
					console.log(f+": input is empty");
				}
				return;
			}
			input.setCustomValidity("");
			input.setAttribute("validity", "pending");
			let validate = function(){
				if(input.willValidate && !input.checkValidity()){
					if(print){
						console.error(f.concat(": Input failed built-in validity check; about to print validity state"));
						console.log(input.validity);
					}
					input.setAttribute("validity", "invalid");
					//badInput
					//customError
					//patternMismatch
					//rangeOverflow
					//rangeUnderflow
					//stepMismatch
					//tooLong
					//tooShort
					//typeMismatch
					//valid
					//valueMissing
					return;
				}else if(input.hasAttribute("__instantValidators")){
					if(print){
						console.log(f+": input has instant validators; about to get comma-separated list");
					}
					let iv = input.getAttribute("__instantValidators");
					if(iv.includes(',')){
						iv = iv.split(',');
					}else{
						iv = [iv];
					}
					for(let i in iv){
						if(print){
							console.log(f.concat(": validator name \"").concat(iv[i]).concat("\""));
						}
						let vc = getApplicationClass().getValidatorClass(iv[i]);
						if(vc === null){
							return error(f, "Invalid validator class \"".concat(iv[i]).concat("\""));
						}
						let validator = new vc();
						if(!validator.instantValidate(event, input)){
							if(print){
								console.log(f.concat(": failed validator \"").concat(iv[i]).concat("\""));
							}
							input.setAttribute("validity", "invalid");
							input.setCustomValidity(validator.getCustomValidationMessage()); //Attribute("__invalid", vc);
							return;
						}else if(print){
							console.log(f.concat(": successfully passed validator class \"").concat(vc).concat("\""));
						}
					}
					if(print){
						console.log(f+": passed all instant validator checks");
					}
				}else if(print){
					console.log(f, "Element does not have __instantValidators attribute");
				}
				if(input.hasAttribute("__ajaxValidator")){
					let iv = input.getAttribute("__ajaxValidator");
					let vc = getApplicationClass().getValidatorClass(iv);
					if(vc === null){
						return error(f, "Invalid validator class \"".concat(vc).concat("\""));
					}
					let validator = new vc();
					if(print){
						console.log(f.concat(": about to fire ajax validator \"").concat(vc).concat("\""));
					}
					return validator.ajaxValidate(event, input);
				}else if(print){
					console.log(f+": input does not have an ajax validator");
				}
				input.setCustomValidity('');
				input.setAttribute("validity", "valid");
				Validator.validateForm(input.form);
			};
			let duration = 1000;
			if(print){
				console.log(f+": about to check input's timeout attribute");
			}
			let timeout;
			if(input.hasAttribute("timeout")){
				if(print){
					console.log(f+": input already has a timeout attribute; about to refresh timeout function");
				}
				timeout = refreshTimeout(
					parseInt(input.getAttribute("timeout")), 
					validate, 
					duration
				);
			}else{
				if(print){
					console.log(f+": input does not have a timeout attribute; about to set timeout function");
				}
				timeout = setTimeout(validate, duration);
			}
			if(print){
				console.log(f+": about to set timeout attribute")
			}
			input.setAttribute("timeout", timeout);
		}catch(x){
			return error(f, x);
		}
	}
	
	static validateForm(form){
		let f = "validateForm()";
		try{
			let inputs = form.elements;
			for(let i = 0; i < inputs.length; i++){
				let input = inputs[i]
				if(!input instanceof Element){
					console.log(i);
					error(f, "One of these things is not an element");
					return;
				}else if(input.hasAttribute('validity') && input.getAttribute('validity') !== 'valid'){
					console.error(f+": at least one of the inputs is invalid or pending");
					form.setAttribute("validity", "invalid");
					return;
				}
			}
			form.setAttribute("validity", "valid");
		}catch(x){
			return error(f, x);
		}
	}
	
	static inputNameToArray(name){
		let f = "inputNameToArray()";
		try{
			let print = false;
			if(!/[a-zA-Z_]+[a-zA-Z0-9_]*(\[[a-zA-Z_]+[a-zA-Z0-9_]*\])+/.test(name)){
				if(print){
					console.log(f.concat(": input name is does not match regular expression"));
				}
				return name;
			}else if(print){
				console.log(f.concat(": input ").concat(name).concat(" matched regular expression"));
			}
			let splat = name.split(/[\[\]]/).filter(Boolean);
			if(print){
				console.log(f.concat(": split name into the following:"));
				console.log(splat);
			}
			return splat[splat.length-1];
			let ret = [];
			ret[splat[splat.length-2]] = splat[splat.length-1];
			/*let i;
			let temp = {};
			for(i = splat.length-2; i >= 0; i--){
				temp = [];
				temp[splat[i]] = ret;
				ret = temp;
				temp = null;
			}*/
			if(print){
				console.log(f.concat(": objectified input name to the following:"));
				console.log(ret);
			}
			return ret;
		}catch(x){
			return error(f, x);
		}
	}
	
	ajaxValidate(event, input){
		let f = this.constructor.name.concat(".instantValidate()");
		try{
			let print = false;
			
			if(print){
				console.log(f.concat(": about to test nested input name objectification"));
				let test = "something[key1][_key2][key3]";
				console.log(Validator.inputNameToArray(test));
			}
			
			let name = Validator.inputNameToArray(input.name);
			let body_unserialized = {
				form:input.form.id,
				name:name,
				value:input.value
			};
			if(input.hasAttribute("id")){
				body_unserialized['id'] = input.id;
			}
			let action = "/validate/".concat(this.constructor.name);
			fetch_xhr("POST", action, body_unserialized, callback_generic, error_cb);
		}catch(x){
			return error(f, x);
		}
	}
}
