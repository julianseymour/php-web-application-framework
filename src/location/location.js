function submitLocation(position){
	let obj = new DataStructure();
	obj.accuracy = position.coords.acccuracy;
	obj.altitude = position.coords.altitude;
	obj.altitudeAccuracy = position.coords.altitudeAccuracy;
	obj.heading = position.coords.heading;
	obj.latitude = position.coords.latitude;
	obj.longitude = position.coords.longitude;
	obj.speed = position.coords.speed;
	obj.timestamp = position.timestamp;
	console.log(obj);
	submitInitializeLocationForm(obj);
}

function getLocation(callback_success, callback_error){
	if(navigator.geolocation){
		navigator.geolocation.getCurrentPosition(callback_success);
	}else{
		InfoBoxElement.showInfoBox("Geolocation API is unsupported");
	}
}
