function getTosAgreement(){//get the click state of the TOS agreement checkbox
	let f = "getTosAgreement()";
	try{
		let box = document.getElementById("agree_tos");
		if(box !== null){
			let c = box.checked;
			if(typeof c === 'undefined'){
				console.error("undefined TOS agreement checkbox state");
				return false;
			}
			return c;
		}else{
			console.error("unable to get TOS agreement checkbox by id");
			return false;
		}
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
	}
}

function getRegisterNoticeElement(){
	let f = "getRegisterNoticeElement()";
	try{
		return document.getElementById("register_notice");
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
		return null;
	}
}

function updateRegisterNotice(notice){
	let f = "updateRegisterNotice()";
	try{
		console.log(f+": entered");
		if(getRegisterNoticeElement().value == "Please wait..."){
			console.log(f+": register notice element is already being updated; assign an event listener here");
			return;
		}
		console.log(f+": register notice element is not in please wait mode");
		replaceInnerHTMLById("register_notice", notice);
	}catch(x){
		error(f, x);
	}
}

function revertRegisterNotice(){
	let f = "revertRegisterNotice()";
	try{
		if(getRegisterNoticeElement().innerHTML != "&nbsp"){
			updateRegisterNotice(default_notice);
		}
	}catch(x){
		error(f, x);
	}
}
