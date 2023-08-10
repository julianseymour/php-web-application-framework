function translate(string_id, substitutions){
	let f = "translate()";
	try{
		let print = false;
		let string = StringTable.getDefaultLanguageString(string_id);
		if(print){
			console.log(f.concat(": translation of string \"").concat(string_id).concat("\" is \"").concat(string).concat("\""));
		}
		if(print){
			console.log(f.concat(": about to translate string \"").concat(string_id).concat("\""));
		}
		if(!empty(substitutions)){
			if(print){
				console.log(f+": about to print subssitutions array");
				console.log(substitutions);
			}
			for(let sub in substitutions){
				if(print){
					console.log(f.concat(": substitution is \"").concat(sub).concat("\""));
					let type = typeof sub;
					console.log(f.concat(": subssitution index has type \"").concat(type).concat("\""));
				}
				let replacement;
				if(typeof substitutions[sub] == "object"){
					if(print){
						console.log(f.concat(": substitution is an object"));
						console.log(substitutions[sub]);
					}
					replacement = substitutions[sub].evaluate();
				}else{
					if(print){
						console.log(f.concat(": substitution is NOT an object"));
					}
					replacement = substitutions[sub];
				}
				if(print){
					console.log(f.concat(": replacement is \"").concat(replacement).concat("\""));
				}
				sub = parseInt(sub);
				if(print){
					console.log(f.concat(": before substitution, atring is now \"").concat(string).concat("\""));
				}
				string = str_replace('%'.concat(sub+1).concat('%'), replacement, string);
				if(print){
					console.log(f.concat(": after substitution, atring is now \"").concat(string).concat("\""));
				}
			}
		}else if(print){
			console.log(f+": substitutions array is empty");
		}
		if(print){
			console.log(f.concat(": returning \"").concat(string).concat("\""));
		}
		return string;
	}catch(x){
		error(f, x);
	}
}

function chainTranslate(string_id, chain){
	let f = "chainTranslate()";
	try{
		let count = chain.length;
		if(count === 1){
			if(typeof(chain[0]) == "string"){
				return translate(string_id, [chain[0]]);
			}
			return translate(string_id, [translate(chain[0])]);
		}
		if(!array_key_exists(0, chain)){
			console.log(chain);
			return error(f, "Undefined index 0");
		}
		return translate(string_id, [chainTranslate(chain[0], array_slice(chain, 1))]);
	}catch(x){
		error(f, x);
	}
}
