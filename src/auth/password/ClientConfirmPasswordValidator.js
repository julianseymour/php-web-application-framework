class ClientConfirmPasswordValidator extends Validator{
	
	instantValidate(event, input){
		let f = this.constructor.name.concat(".instantValidate()");
		try{
			let print = false;
			if(print){
				console.log(f+": entered");
			}
			if(!input.hasAttribute("__match")){
				return error(f, "Input does not have a counterpart name attribute");
			}
			let name = input.getAttribute("__match");
			if(print){
				console.log(f.concat(": other input name is \"").concat(name).concat("\""));
			}
			let other_input = input.form.elements[input.getAttribute("__match")];
			if(print){
				if(input.value === other_input.value){
					console.log(f+": match successful");
				}else{
					console.log(f+": match failed");
				}
			}
			return input.value === input.form.elements[input.getAttribute("__match")].value;
		}catch(x){
			return error(f, x);
		}
	}
	
	getCustomValidationMessage(){
		return STRING_PASSWORDS_MUST_MATCH;
	}
	
	static changePassword(event, input){
		let f = "changePassword()";
		try{
			let print = false;
			if(print){
				console.log(f+": entered");
			}
			if(empty(input.value)){
				if(print){
					console.log(f+": input is empty -- hiding validity indicator");
				}
				input.setAttribute("validity", "hidden");
			}else{
				if(print){
					console.log(f+": input is not empty -- validating");
				}
				Validator.instantValidateStatic(event, input);
			}
			let form = input.form;
			let counterpart = form.elements[input.getAttribute("counterpartName")];
			Validator.instantValidateStatic(event, counterpart);
		}catch(x){
			return error(f, x);
		}
	}
}
