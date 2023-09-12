function luhn(imei){
	let f = "luhn("+imei+")";
	try{
		//console.log(f+" entered");
		let sum = 0;
		let check = imei % 10;
		imei = Math.floor(imei/10);
		let double = true;
		while (imei > 9){
			let digit = imei % 10;
			//console.log(f+": current digit is "+digit);
			if(double){
				digit *= 2;
				//console.log(f+": digit is now "+digit);
				if(digit > 9) digit -= 9;
			}
			double = double ? false : true;
			sum += digit;
			imei = Math.floor(imei/10);
			//console.log(f+": sum is now "+sum+"; imei is now "+imei);
		}
		sum += imei;
		if((sum*9)%10==check && (sum+check)%10==0){
			//console.log(f+": check digit "+check+" OK");
			return true;
		}else{
			//console.error(f+": FAILED (sum "+sum+", check digit "+check+")");
			return false;
		}
	}catch(x){
		console.error(f+" exception: \""+x.toString()+"\"");
		return false;
	}
}
