class InfoBoxElement extends Basic{

	static updateInfoBox(content, callback_success, callback_error){
		let f = "updateInfoBox()";
		try{
			console.trace();
			window.alert(f+": currently dismantling");
			showInfoBox(content, callback_success, callback_error);
		}catch(x){
			error(f, x);
		}
	}

	static showInfoBox(content, callback_success, callback_error){
		let f = "InfoBoxElement.showInfoBox()";
		try{
			let print = false;
			if(print){
				console.log(f+": entered; about to log infobox content");
				console.log(content);
			}
			
			if(!isset(callback_success)){
				if(print){
					let err = "callback_success is undefined";
					//return error(f, err);
					console.log(f.concat(": ").concat(err));
				}
			}else if(typeof callback_success !== "function"){
				
				console.error(f.concat(": about to log callback_success: \"").concat(callback_success).concat("\""));
				
				let err = "callback_success is not a function";
				return error(f, err);
			}else if(print){
				console.log(f+": callback_success does exist, and it's a function");
			}
			if(!elementExists("info_box_check")){
				return error(f, "Info box checkbox does not exist");
			}
			let box = document.getElementById("info_box_check");
			let replaceme = document.getElementById("info_box_replace");
			if(box.checked == false){
				console.log(f+": infobox is not open"); //about to call showInfoBox");
				setTimeout(function(){
					console.log(f+": about to replace info box");
					replaceInnerHTML(replaceme, content, callback_success, callback_error);
					console.log(f+": called replaceInnerHTML");
					let box = document.getElementById("info_box_check");
					if(box.checked == true){
						console.log(f+": checkbox is already checked; returning");
					}else{
						console.log(f+": replaced info box; about to click its box");
						box.dispatchEvent(new Event('change'));
						box.checked = true;
						console.log(f+": checked the checkbox");
					}
				}, 500);
			}else{
				console.log(f+": infobox is already open");
				replaceInnerHTML(replaceme, content, callback_success, callback_error);
			}
		}catch(x){
			error(f, x);
		}
	}

	static resetInfoBox(event=null, clicked=null){
		let f = "resetInfoBox()";
		try{
			if(event){
				event.preventDefault();
			}
			//replaceInnerHTMLById("info_box_replace", "&nbsp", callback_success, callback_failure);
			/*if(box.checked){
				console.log(f+": about to uncheck the checkbox");
				box.removeAttribute("checked");
				box.checked = false;
				console.log(f+": unchecked the checkbox");
			}else{
				console.log(f+": checkbox is already unchecked");
			}*/
			replaceInnerHTMLById("info_box_replace", "&nbsp", null);
			let checkbox = document.getElementById("info_box_check");
			if(checkbox.checked){
				console.log(f+": checkbox is checked");
				checkbox.checked = false;
				//clearInfoBox();
			}else{
				console.log(f+": checkbox is unchecked");
			}
		}catch(x){
			console.error(f+" exception: "+x.toString());
		}
	}

	/*static clearInfoBox(){
		let f = "clearInfoBox()";
		try{
			/*if(info_box_check.checked){
				console.log(f+": checkbox is checked");
			}else{
				console.log(f+": checkbox is unchecked");
				
			}*/
			/*replaceInnerHTMLById("info_box_replace", "&nbsp", null);
		}catch(x){
			return error(f, x);
		}
	}*/
	
	/*static initializeInfoBox(){
		let f = "initializeInfoBox()";
		try{
			console.log(f+": entered");
			console.trace();
			let info_box_container = document.getElementById("info_box_container");
			info_box_container.style['display'] = "";
			//let info_box_check = document.getElementById("info_box_check");
			//info_box_check.addEventListener("change", reset_clear);	
			//console.log(f+": initialized infobox");
		}catch(x){
			console.error(f+" exception: "+x.toString());
		}
	}*/
}