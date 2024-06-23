class AjaxForm extends Basic{
	
	static generateGenericButton(name, value=null, form=null, context=null){
		let f = "AjaxForm.generateGenericButton()";
		try{
			let button = document.createElement("button");
			button.name = "directive"; //name;
			if(form != null){
				let form_id = form.id;
				let id = name.concat("-").concat(form_id);
				if(value != null){
					button.value = "".concat(name).concat("[").concat(value).concat("]");
					id = id.concat("-").concat(NameDatum.normalize(value));
				}
				button.id = id;
			}
			button.onclick = function(event){
				AjaxForm.appendSubmitterName(event, button);
			};
			button.type = "submit";
			/*if(form != null){
				button.form = form;
			}*/
			let pretty = null;
			/*if(context.hasPrettyClassName()){
				pretty = context.getPrettyClassName();
			}*/
			let innerHTML = null;
			switch(name){
				case DIRECTIVE_IMPORT_CSV:
					innerHTML = STRING_IMPORT_CSV_FILES;
					break;
				case DIRECTIVE_INSERT:
					innerHTML = STRING_SUBMIT;
					break;
				case DIRECTIVE_REGENERATE:
				case DIRECTIVE_UNSET:
				case DIRECTIVE_UPDATE:
					innerHTML = STRING_UPDATE;
					break;
				case DIRECTIVE_DELETE:
				case DIRECTIVE_DELETE_FOREIGN:
				case DIRECTIVE_MASS_DELETE:
					innerHTML = STRING_DELETE;
					break;
				case DIRECTIVE_EMAIL_CONFIRMATION_CODE:
					innerHTML = STRING_SEND_CONFIRMATION_CODE;
					break;
				case DIRECTIVE_READ:
				case DIRECTIVE_READ_MULTIPLE:
					innerHTML = STRING_READ;
					break;
				case DIRECTIVE_REFRESH_SESSION:
					innerHTML = STRING_REFRESH__SESSION;
					break;
				case DIRECTIVE_SEARCH:
					innerHTML = STRING_SEARCH;
					break;
				case DIRECTIVE_SELECT:
					innerHTML = STRING_SELECT;
					break;
				case DIRECTIVE_NONE:
				case DIRECTIVE_SUBMIT:
					innerHTML = STRING_SUBMIT;
					break;
				case DIRECTIVE_UPLOAD:
					innerHTML = STRING_UPLOAD;
					break;
				case DIRECTIVE_VALIDATE:
					innerHTML = STRING_VALIDATE;
					break;
				default:
					return error(f, "Invalid name attribute \"".concat(name).concat("\""));
			}
			button.innerHTML = innerHTML;
			return button;
		}catch(x){
			return error(f, x);
		}
	}
	
	static generateEditButtons(form, context){
		let f = "generateEditButtons()";
		try{
			let directives;
			if(!context.hasIdentifierValue()){
				directives = [DIRECTIVE_INSERT];
			}else if(getCurrentUserAccountType() === ACCOUNT_TYPE_ADMIN){
				directives = [
					DIRECTIVE_UPDATE,
					DIRECTIVE_DELETE
				];
			}else if(context.getColumnValue("userKey") === getCurrentUserKey()){
				directives = [DIRECTIVE_UPDATE];
			}else{
				return [];
			}
			let buttons = [];
			for(let dir of directives){
				let button = AjaxForm.generateGenericButton(dir, null, form, context);
				buttons.push(button);
			}
			return buttons;
		}catch(x){
			return error(f, x);
		}
	}
	
	static terminateLoadAnimation(id){
		let f = "terminateLoadAnimation("+id+")";
		try{
			if(id == null){
				let err = f+": received null parameter";
				console.error(err);
				AjaxForm.showInfoBox(err)
				console.trace();
				console.log(err);
				return;
			}else if(!elementExists(id)){
				return error(f, "Element with Id \"".concat(id).concat("\""));
			}
			//console.log(f+": element does exist");
			
			let load_c = document.getElementById(id);
			replaceInnerHTMLById(id, "&nbsp");
			let attr_button = load_c.getAttribute("button");
			if(empty(attr_button)){
				let err = "Button ID attribute not found";
				console.log(err);
				console.log(Error().stack);
			}else{
				let button_element = document.getElementById(attr_button);
				if(button_element == null){
					console.error(f.concat(": button element with id \"").concat(attr_button).concat("\" is undefined"));
					return;
				}
				
				//console.log(f+": got button to reenable; about to check its temporary ID status");
				let temp = button_element.getAttribute("temp_btn");
				if(temp == null || temp == ""){
					//console.log(f+": button does not have a temporary ID");
				}else{
					//console.log(f+": button has the temporary ID \""+temp+"\"; removing it now");
					button_element.id = null;
					button_element.removeAttribute("temp_btn");
				}
			}
			//button_element.removeAttribute("disabled");
			//button_element.style["cursor"] = "auto";
			//button_element.style['pointer-events'] = "auto";
			//console.log(f+": returning normally");
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
		}
	}
	
	/**
	 * Click handler to be placed on submit inputs for forms submitted with submitForm
	 * Disable the provided input, insert a loading animation and hidden copy of the input's name into the disposable load container
	 * The inserted elements will be removed in terminateLoadAnimation, which is invoked automatically with the callbacks provided to setFormSubmitHandler
	 * @param submitter input[type="submit"]
	 * @returns void
	 */
	static appendSubmitterName(event, submitter){
		let f = "AjaxForm.appendSubmitterName()";
		try{
			let print = false;
			if(print){
				console.log(f+": entered");
			}
			if(!isset(submitter)){
				console.trace();
				console.log(f+": you submat null");
				return;
			}else if(submitter.form.hasAttribute("disabled")){
				return error(f, "Submit button is disabled");
			}else if(typeof(submitter.form) == "undefined"){
				return error(f, "Submitter for is undefined");
			}else if(print){
				let form = submitter.form;
				form.style['background-color'] = "#0f0";
				let form_id = form.id;
				console.log(f.concat(": submit button's form name is \"").concat(form_id).concat("\""));
			}
			if(print){
				console.log(f+": submitter is object of class "+submitter.constructor.name);
				console.log(submitter.form.onsubmit.toString());
			}
			let form = submitter.form;
			if(print){
				let str = "";
				for(let i = 0; i < submitter.attributes.length; i++){
					str += submitter.attributes[i].name.concat(" : ").concat(submitter.attributes[i].value).concat("\n");
				}
				console.log(str);
			}
			let evh = form.onsubmit;
			if(!isset(evh)){//XXX find a way to circumvent this if it's one of the few forms that naturally does not have a submit event handler
				if(form.getAttribute("nosubmit") == 1){
					if(print){
						console.log(f+": this is an uninitialized static form");
					}
				}else{
					event.preventDefault();
					error(f, "form ".concat(form.id).concat("lacks a submit event handler"));
					return false;
				}
			}else if(print){
				console.log(f+": about to log form's event handler");
				console.log(evh);
			}
			let valid = true;
			let inputs = form.elements;
			for(let i = 0; i < inputs.length; i++){
				let input = inputs[i];
				if(input.required && !input.validity.valid){
					if(print){
						console.log(f.concat(": input #").concat(i).concat(" \"").concat(input.name).concat("\" is invalid"));
						console.log(input);
					}
					valid = false;
					break;
				}
			}
			if(valid){
				if(print){
					console.log(f+": about to get loading container");
				}
				let load_c_id = "load_".concat(submitter.form.id);
				let load_c = document.getElementById(load_c_id);
				if(load_c == null){
					let err = "Load container with id \"".concat(load_c_id).concat("\" returned null");
					event.preventDefault();
					return error(f, err);
				}else if(print){
					console.log(f+": load container acquired");
				}
				let input = AjaxForm.createLoadContainerInput(submitter);
				let loading = AjaxForm.createLoadAnimationElement();
				load_c.appendChild(input);
				load_c.appendChild(loading);
				
				if(!empty(document.getElementById("pushSubscriptionKey").value)){
					load_c.appendChild(generatePushSubscriptionKeyInput())
				}
				
				if(submitter.id == null || submitter.id == ""){
					if(print){
						console.log(f+": submitter ID is null or undefined -- generating one now");
					}
					submitter.id = "temp_btn-".concat(submitter.form.id.concat(submitter.getAttribute("name")));
					submitter.setAttribute('temp_btn', 1);
				}else if(print){
					console.log(f+": submitter ID is \""+submitter.id+"\"");
				}
				load_c.setAttribute("button", submitter.id);
				if(print){
					console.log(f.concat(": set button attribute to \"").concat(submitter.id).concat("\" for load container \"").concat(load_c.id+"\""));
				}
			}else{
				event.preventDefault();
			}
		}catch(x){
			error(f, x);
		}
	}

	static setFormSubmitHandler(form, callback_success, callback_error){
		let f = "AjaxForm.setFormSubmitHandler()";
		try{
			let print = false;
			if(print){
				console.log(f+": entered");
			}
			let err;
			if(form == null){
				err = f+": form is undefined";
				return error(f, err);
			}else if(typeof(callback_success) === "string"){
				if(print){
					err = f.concat(": Function \"").concat(callback_success).concat("\" is just a string, better hope it's the name of a function");
					console.log(err);
				}
				callback_success = window[callback_success];
				if(typeof(callback_success) !== "function"){
					console.error(callback_success);
					return error(f, "callback_success is not a function");
					//console.error(f+": going to see what happens anyway");
				}else if(print){
					console.log(f+": yes, it's a function");
				}
			}else if(typeof(callback_success) !== "function"){
				err = "Function \"".concat(callback_success).concat("\" is not a function");
				return error(f, err);
			}else if(print){
				console.log(f+": callback_success is a regular function");
			}
			let form_id = form.id;
			if(form_id == null || form_id == ""){
				if(print){
					console.log(form);
				}
				//form.style['background-color'] = "#f00 !important";
				return error(f, "Form ID is null");
			}
			let evh = form.onsubmit;
			if(evh == null){
				if(print){
					console.log(f+": before initialization, form's submit event is undefined");
				}
			}else{
				let x = "Form \"".concat(form_id).concat("\" already has an event handler assigned");
				console.error(x);
			}
			if(print){
				console.log(f+": form has ID \""+form.id+"\"");
			}
			form.onsubmit = function(event){
				try{
					event.preventDefault();
					form.setAttribute("disabled", "1");
					let f = "submit event listener";
					let print = false;
					if(print){
						let err = f+": about to submit form ID \""+form_id+"\"";
						window.alert(err);
						console.log(f+": event target is type "+typeof event.target);
					}
					AjaxForm.submitForm(form, 
						function(response){
							let f = "submit event listener success callback"
							let load_id = "load_".concat(form_id);
							if(empty(load_id)){//} == null || load_id == ""){
								load_id = "load_".concat(form.id);
								if(elementExists(load_id)){
									if(print){
										console.log(f+": loading container \"".concat(load_id).concat("\" found autonatically"));
									}
								}else{
									let err = f+": form with ID \""+form_id+"\" does not have a loading attribute";
									return error(f, err);
								}
							}else if(print){
								console.log(f.concat(": load ID is \"").concat(load_id).concat("\""));
							}
							if(print){
								console.log(f+": form submitted successfully; about to terminate loading animation on loading container "+load_id);
							}
							let load_c = document.getElementById(load_id);
							form.removeAttribute("disabled");
							AjaxForm.terminateLoadAnimation(load_id);
							resetSessionTimeoutAnimation(true);
							let process = function(response, callback_1, callback_2){
								if(response.hasCommands()){
									if(print){
										console.log(f+": about to call process media commands");
									}
									response.processCommands(callback_1, callback_2);
								}
							};
							if(typeof(callback_success) !== "function"){
								console.error(callback_success);
								return error(f, "callback_success is not a function");
							}
							if(print){
								console.log(f+": about to call callback_success");
							}
							callback_success(response, process, callback_error);
						}, 
						function(response){
							let f = "submit event listener error callback"
							let load_id = "load_".concat(form_id);
							console.error(f+": form submission failed; about to terminate loading animation on container \""+load_id+"\"");
							form.removeAttribute("disabled");
							AjaxForm.terminateLoadAnimation(load_id);
							callback_error(form);
						}
					);
					if(print){
						window.alert(f+": submitted form \""+form_id+"\"");
					}
				}catch(x){
					console.error(f+": exception thrown inside submit handler: \""+x.toString()+"\"");
				}
			};
			evh = form.onsubmit;
			if(typeof evh == 'undefined'){
				if(print){
					console.log(f+": form's submit event is undefined");
				}
				return;
			}
			//form.style['background-color'] = '#f00 !important';
			if(print){
				console.log(f+": assigned form submit handler; returning true");
			}
			return true;
		}catch(x){
			console.error(f+": exception: \""+x.toString()+"\"");
			return false;
		}
	}
	
	static createLoadContainerInput(submitter){
		let f = "createLoadContainerInput()";
		try{
			let input = document.createElement("input");
			input.type = "hidden";
			input.name = submitter.getAttribute("name");
			let value = submitter.getAttribute("value");
			if(value == null || value == ""){
				return error(f, "Submit button does not have a value attribute");
				//value = 1;
			}
			input.value = value;
			return input;
		}catch(x){
			error(f, x);
		}
	}

	//XXX TODO this should be a generated template function
	static createLoadAnimationElement(){
		let f = "createLoadAnimationElement()";
		try{
			let c = document.createElement("div");
			c.classList.add("form_load_c");
			let bg = document.createElement("div");
			bg.classList.add("form_load_bg");
			bg.classList.add("background_color_1")
			c.appendChild(bg);
			let loading = document.createElement("div");
			loading.classList.add('form_load_anim');
			c.appendChild(loading);
			return c;
		}catch(x){
			error(f, x);
		}
	}

	/**
	 * submit a form through XHR and act upon the response text
	 * this cannot be declared inline in setSubmitFormHandler because the messenger also uses it without going through the load animation sequence
	 * @param form : form element to submit
	 * @param callback_success : callback for when the XHR is successful
	 * @param callback_error : callback invoked if an error occurs
	 * @returns void
	 */
	static submitForm(form, callback_success, callback_error){
		let f = "AjaxForm.submitForm()";
		try{
			let print = false;
			if(print){
				console.log(f+": entered");
			}
			if(typeof(callback_success) !== "function"){
				console.error(callback_success);
				return error(f, "callback_success is not a function");
			}
			//console.log(form);
			let formdata = new FormData(form);
			//formdata.append("js", 1);
			let method = form.getAttribute("method");
			//console.log(f+": appended form data; about to add load event listener");
			let action = form.getAttribute("action");
			if(print){
				window.alert(f+" form.action: \""+action+"\"");
			}
			fetch_xhr(method, action, formdata, callback_success, callback_error);
		}catch(x){
			console.error(f+" exception: "+x.toString());
		}
	}
	
	static initializeAllForms(){
		let f = "initializeAllForms()";
		try{
			let print = false;
			let forms = document.querySelectorAll("form.ajax_form");
			if(print){
				console.log(forms);
			}
			for(let i = 0; i < forms.length; i++){
				let form = forms[i];
				if(print){
					//console.log(form);
				}
				if(!isElement(form) || form.tagName.toLowerCase() !== "form"){
					console.error(f.concat(": element ").concat(i).concat(" is not a form"));
					console.log(form);
					continue;
				}else if(!!form.onsubmit){
					if(print){
						console.log(
							f.concat(": form \"").concat(form.id).concat("\" already has an onsubmit")
						);
					}
					continue;
				}else if(
					!form.hasAttribute("callback_success") 
					&& !form.hasAttribute("callback_error")
				){
					if(print){
						console.log(f.concat(": form \"").concat(form.id).concat("\" does not have an onsubmit, but it also doesn't specify success/error callbacks"));
					}
					continue;
				}
				let callback_success = form.hasAttribute("callback_success") ? form.getAttribute("callback_success") : "controller";
				let callback_error = form.hasAttribute("callback_error") ? form.getAttribute("Callback_error") : "error_cb";
				if(print){
					console.log(f.concat(": form \"").concat(form.id).concat("\" does not have an onsubmit, and it specifies success callback \"").concat(callback_success).concat("\" and error callback \"").concat(callback_error).concat("\""));
				}
				AjaxForm.setFormSubmitHandler(form, callback_success, callback_error);
			}
		}catch(x){
			error(f, x);
		}
	}
	
	static setUniversalFormAction(action){
		let f = "AjaxForm.setUniversalFormAction()";
		try{
			let forms = document.querySelectorAll("form.universal_form");
			for(let i = 0; i < forms.length; i++){
				let form = forms[i];
				form.setAttribute("action", action);
			}
		}catch(x){
			error(f, x);
		}
	}
}
