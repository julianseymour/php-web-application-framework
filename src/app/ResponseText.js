class ResponseText extends Basic{
	
	constructor(body, callback){
		//super(body, callback);
		let f = "ResponseText constructor()";
		try{
			let print = false;
			let parsed;
			if(isset(body)){
				if(typeof body == 'string'){
					if(print){
						console.log(f.concat(": body is a string"));
					}
					parsed = JSON.parse(body);
				}else{
					if(print){
						console.log(f.concat(": body is not a string, serializing it now"));
					}
					parsed = body;
					body = JSON.stringify(body);
				}
				if(print){
					console.log(f+": about to log parsed input parameter");
					console.log(parsed);
					//window.alert(f+": logged input parameter");
				}
			}else{
				if(print){
					console.log(f.concat(": body is undefined"));
				}
				parsed = null;
			}
			super(parsed);
			if(isset(body)){
				this.body = body;
				this.dataStructures = [];
				if(!empty(parsed.dataStructures)){
					if(print){
						console.log(f.concat(": about to allocate data structures"));
					}
					getApplicationClass().allocateDataStructures(parsed.dataStructures, this);
				}else if(print){
					console.log(f+": no data structures to allocate");
					/*console.trace();
					console.log(body);
					window.alert(f+": logged input parameter");*/
				}
				if(isset(parsed.commands)){
					if(print){
						console.log(f+": about to set commands");
					}
					this.setCommands(ResponseText.createCommands(parsed.commands, this));
				}else if(print){
					console.error(f+": no commands to allocate");
				}
			}else if(print){
				console.log(f.concat(": body is undefined"));
			}
			if(typeof callback == "function"){
				if(print){
					console.log(f+": about to defer callback");
				}
				let rt = this;
				defer(function(){
					callback(rt);
				});
			}
			if(print){
				console.log(f+": constructor finished");
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	getDataStructures(){
		return this.dataStructures;
	}
	
	getCommands(){
		return this.commands;
	}
	
	hasSuccessCallback(){
		return isset(this.successCallback) && typeof this.successCallback == 'function';
	}
	
	hasCommands(){
		return !empty(this.commands);
	}

	hasDataStructure(key){
		return this.dataStructures.hasOwnProperty(key);
	}

	getDataStructure(key){
		let f = this.constructor.name.concat(".getDataStructure()");
		try{
			let d = this.dataStructures[key];
			if(!isset(d)){
				return error(f, "Data structure with key \"".concat(key).concat("\" is undefined"));
			}
			return d;
		}catch(x){
			console.error(this);
			return error(f, x);
		}
	}

	setDataStructure(key, struct){
		let f = this.constructor.name.concat(".setDataStructure()");
		try{
			let print = false;
			if(print){
				console.log(f.concat(": assigning a data structure to key \"").concat(key).concat("\""));
				console.log(struct);
			}
			return this.dataStructures[key] = struct;
		}catch(x){
			return error(f, x);
		}
	}

	getCorrespondentKey(){
		let f = this.constructor.name.concat(".getCorrespondentKey()");
		if(!this.hasCorrespondentKey()){
			return error(f, "Correspondent key is undefined");
		}
		return this.correspondentKey;
	}

	hasCurrentUserKey(){
		let f = this.constructor.name.concat(".hasCurrentUserKey()");
		return isset(this.currentUserKey);
	}
	
	getCurrentUserKey(){
		let f = this.constructor.name.concat(".getCurrentUserKey()");
		try{
			if(!this.hasCurrentUserKey()){
				return error(f, "Current user key is undefined");
			}
			return this.currentUserKey;
		}catch(x){
			error(f, x);
		}
	}
	
	hasCurrentUserData(){
		let f = this.constructor.name.concat(".hasCurrentUserData()");
		try{
			return this.hasCurrentUserKey() && this.hasDataStructure(this.getCurrentUserKey());
		}catch(x){
			return error(f, x);
		}
	}
	
	user(){
		let f = this.constructor.name.concat(".user()");
		try{
			if(!this.hasCurrentUserData()){
				if(this.hasCurrentUserKey()){
					console.log("Current user key is defined, but not data structure");
				}else{
					console.log("Current user key is undefined");
				}
				return error(f, "Current user data is undefined");
			}
			return this.getDataStructure(this.getCurrentUserKey());
		}catch(x){
			return error(f, x);
		}
	}
	
	setCorrespondentKey(ck){
		return this.correspondentKey = ck;
	}

	hasCorrespondentKey(){
		return isset(this.correspondentKey);
	}

	hasRemainingObjectCount(){
		return isset(this.remainingObjectCount);
	}

	getRemainingObjectCount(){
		let f = this.constructor.name.concat(".getRemainingObjectCount()");
		if(!this.hasRemainingObjectCount()){
			return error(f, "Remaining object count is undefined");
		}
		return this.remainingObjectCount;
	}

	setRemainingObjectCount(count){
		return this.remainingObjectCount = count;
	}
	
	hasLastLoadedSerialNumber(){
		return isset(this.lastLoaded);
	}
	
	setLastLoadedSerialNumber(last){
		return this.lastLoaded = last;
	}
	
	getLastLoadedSerialNumber(){
		let f = this.constructor.name.concat(".getLastLoadedSerialNumber()");
		if(!this.hasLastLoadedSerialNumber()){
			return error(f, "Last loaded object number is undefined");
		}
		return this.lastLoaded;
	}
	
	hasIntent(){
		return isset(this.intent);
	}
	
	getIntent(){
		let f = this.constructor.name.concat(".getIntent()");
		try{
			if(!this.hasIntent()){
				return error(f, "Intent is undefined");
			}
			return this.intent;
		}catch(x){
			return error(f, x);
		}
	}
	
	getPushedNotificationData(){
		let f = this.constructor.name.concat(".getPushedNotificationData()");
		try{
			for(let key in this.getDataStructures()){
				let struct = this.getDataStructure(key);
				if(!isset(struct)){
					return error(f, "Data structure with key \"".concat(key).concat("\" is undefined"));
				}
				let type = struct.getDataType();
				if(type !== DATATYPE_NOTIFICATION){
					console.log(f.concat(": skipping over a data structure of type \"").concat(type).concat("\""));
					continue;
				}
				return struct;
			}
			console.error(f+": no pushable notification data");
		}catch(x){
			return error(f, x);
		}
	}
	
	getReservedPropertyNames(){
		return ["dataStructures", "commands"];
	}
	
	getTemporaryDocument(){
		if(isset(this.temporaryDocument)){
			return this.temporaryDocument;
		}
		return this.temporaryDocument = document.implementation.createHTMLElement("temp");
	}
	
	getBody(){
		return this.body;
	}
	
	setCommands(mc){
		return this.commands = mc;
	}
	
	static createCommands(commands_raw, response){
		let f = "createCommands()";
		try{
			let print = false;
			if(print){
				console.log(f+": received the following parameters:");
				console.log(commands_raw);
			}
			let commands = [];
			for(let index in commands_raw){
				commands[index] = Command.createCommand(commands_raw[index], response);
			}
			if(print){
				console.log(f+": returning the following commands:");
				console.log(commands);
			}
			return commands;
		}catch(x){
			return error(f, x);
		}
	}
	
	processCommands(callback_success, callback_error){
		let f = "processCommands()";
		try{
			if(!this.hasCommands()){
				let err = "commands are undefined; please don't call this unless there are actually commands to process";
				return error(f, err);
			}
			//let response = this;
			//let length = this.commands.length;
			for(let index in this.commands){
				/*if(typeof callback_success == "function" && index == length-1){
					console.log(this.commands[index]);
					console.log(f+": index == length-1 -- passing deferred callback");
					this.commands[index].execute(callback_success);
				}else{
					console.log(f.concat(": executing command at index \"").concat(index).concat("\""));*/
					this.commands[index].execute();
				//}
			}
			/*let defer_me = function(){
				if(isset(callback_success)){
					//console.log(f+": yes, there is a success callback");
					callback_success(response);
				}else{
					//console.log(f+": no, there is no success callback");
				}
			}
			defer(defer_me);*/
		}catch(x){
			return error(f, x);
		}
	}
}
