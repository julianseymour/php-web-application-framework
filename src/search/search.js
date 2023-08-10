function closeSearchResults(clearButtonId, queryInputId, searchResultsId, checkInputId){
	let f = "closeSearchResults()";
	try{
		document.getElementById(clearButtonId).style['opacity'] = 0;
		document.getElementById(queryInputId).value = "";
		replaceInnerHTMLById(searchResultsId, "");
		checkInputWithId(checkInputId);
	}catch(x){
		return error(f, x);
	}
}

function instantSearch(event, input){
	let f = "instantSearch()";
	try{
		console.log(f+": entered");
		if(event == null && input == null){
			console.log(f+": yes, this function exists");
			return;
		}
		if(input.value == "" || input.value == null){
			console.log(f+": input is blank");
			return;
		}
		let input_id = input.id;
		console.log(f.concat(": input ID is \"").concat(input_id).concat("\"; about to get form"));
		let form = input.form;
		let checkbox = document.getElementById("autosearch-".concat(form.id));
		if(!checkbox.checked){
			console.log(f+": autosearch is not enabled for this form");
			return;
		}
		let auto_search = function(){
			console.log(f+": autosearch enabled; about to get submit button");
			let submitter = document.getElementById("search-".concat(form.id));
			let appendme = AjaxForm.createLoadContainerInput(submitter);
			let callback_success = function(response){
				console.log(f+": about to remove timeout attribute from form");
				form.removeAttribute("timeout");
				console.log(f+": about to get success callback from form");
				let cb = window[form.getAttribute("callback_success")];
				cb(response);
			}
			let callback_error = function(){
				console.log(f+": inside the error callback; about to remove timeout attribute");
				form.removeAttribute("timeout");
				error_cb(form);
			};
			console.log(f+": about to append input to loading container");
			document.getElementById("load_".concat(form.id)).appendChild(appendme);
			console.log(f+": about to submit form");
			AjaxForm.submitForm(form, callback_success, callback_error);
		};
		let duration = 2000;
		console.log(f+": about to check form's timeout attribute");
		let timeout;
		if(form.hasAttribute("timeout")){
			console.log(f+": form already has a timeout attribute; about to refresh timeout function");
			timeout = refreshTimeout(
				parseInt(form.getAttribute("timeout")), 
				auto_search, 
				duration
			);
		}else{
			console.log(f+": form does not have a timeout attribute; about to set timeout function");
			timeout = setTimeout(auto_search, duration);
		}
		console.log(f+": about to set timeout attribute")
		form.setAttribute("timeout", timeout);
	}catch(x){
		return error(f, x);
	}
}

//instantSearch(null, null);
