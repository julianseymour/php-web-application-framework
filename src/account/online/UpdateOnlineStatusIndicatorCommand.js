class UpdateOnlineStatusIndicatorCommand extends Command{
	
	static updateStatic(key, status, custom_str, process_subcommands){
		let f = "UpdateOnlineStatusIndicatorCommand.updateStatic()";
		try{
			let conversation_online = document.getElementById("conversation_online-".concat(key));
			let notification_online = document.getElementById("notification_online-".concat(key));
			if(!isset(conversation_online) && !isset(notification_online)){
				//console.log(f+": neither conversation or notification label online indicators exists");
				if(typeof process_subcommands == "function"){
					process_subcommands();
				}
				return;
			}
			let emoji = null;
			let string = null;
			let color = null;
			switch(status){
				case ONLINE_STATUS_UNDEFINED:
					console.error(f+": undefined messenger status");
					emoji = "💀 ";
					string = "ERROR";
					color = "#f00";
					break;
				case ONLINE_STATUS_NONE:
					//console.log(f+": correspondent does not share their online status, good for them");
					if(isset(conversation_online)){
						conversation_online.style['opacity'] = 0;
					}
					if(isset(notification_online)){
						notification_online.style['opacity'] = 0;
					}
					if(typeof process_subcommands == "function"){
						process_subcommands();
					}
					return;
				case ONLINE_STATUS_OFFLINE:
					//console.log(f+": correspondent is offline");
					emoji = "😴 ";
					string = STRING_OFFLINE;
					color = "#555";
					break;
				case ONLINE_STATUS_ONLINE:
					//console.log(f+": correspondent was online recently");
					emoji = "⬤ ";
					string = STRING_ONLINE;
					color = "#0c0";
					break;
				case ONLINE_STATUS_APPEAR_OFFLINE:
					//console.log(f+": correspondent is pretending to be offline");
					emoji = "👻 ";
					string = STRING_APPEAR_OFFLINE;
					color = "#555";
					break;
				case ONLINE_STATUS_AWAY:
					//console.log(f+": correspondent is away");
					emoji = "⚠️ ";
					string = STRING_AWAY;
					color = "#ff0";
					break;
				case ONLINE_STATUS_BUSY:
					//console.log(f+": correspondent is busy");
					emoji = "🛑 ";
					string = STRING_BUSY;
					color = "#f00";
					break;
				case ONLINE_STATUS_CUSTOM:
					//console.log(f+": correspondent has custom messenger status");
					emoji = "";
					string = custom_str;
					color = "#0c0";
					break;
				default:
					return error(f, "Invalid messenger status ".concat(data.online_status));
			}
			let innerHTML = emoji.concat(string);
			if(isset(conversation_online)){
				conversation_online.style['color'] = color;
				conversation_online.innerHTML = innerHTML;
				conversation_online.style['opacity'] = 1;
			}
			if(isset(notification_online)){
				notification_online.style['color'] = color;
				notification_online.innerHTML = innerHTML;
				notification_online.style['opacity'] = 1;
			}
		}catch(x){
			return error(f, x);
		}
	}
	
	execute(){
		let f = this.constructor.name.concat(".execute()");
		try{
			UpdateOnlineStatusIndicatorCommand.updateStatic(
				this.uniqueKey, 
				this.status,
				this.custom_str, 
				function(){
					this.processSubcommands();
				}
			);
		}catch(x){
			return error(f, x);
		}
	}
}
