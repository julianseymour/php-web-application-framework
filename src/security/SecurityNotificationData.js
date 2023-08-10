class SecurityNotificationData extends NotificationData{
	
	static bindNotificationElement(note_data){
		note_data.setColumnValue("widget", "notifications");
		return bindSecurityNotificationElement(note_data);
	}
	
	static getNotificationTypeString(){
		return STRING_SECURITY;
	}
}
