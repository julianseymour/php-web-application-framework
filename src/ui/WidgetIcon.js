class WidgetIcon extends Basic{

	static hideFloatingWidgets(){
		let f = "hideFloatingWidgets()";
		try{
			let widgets = document.querySelectorAll(".widget_icon");
			for(let i = 0; i < widgets.length; i++){
				let widget = widgets[i];
				if(!isElement(widget)){
					console.error(f.concat(": element ").concat(i).concat(" is not an element"));
					console.log(widget);
					continue;
				}
				WidgetIcon.hideWidget(widget.id);
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			return;
		}
	}

	static unhideFloatingWidgets(){
		let f = "unhideFloatingWidgets()";
		try{
			let widgets = document.querySelectorAll(".widget_icon");
			for(let i = 0; i < widgets.length; i++){
				let widget = widgets[i];
				if(!isElement(widget)){
					console.error(f.concat(": element ").concat(i).concat(" is not an element"));
					console.log(widget);
					continue;
				}
				WidgetIcon.unhideWidget(widget.id);
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			return;
		}
	}

	static hideWidget(id){
		let f = "hideWidget("+id+")";
		try{
			let w = document.getElementById(id);
			if (w == null){
				console.error(f+": element with ID \""+id+"\" not found");
			}else{
				setElementOpacity(w, 0);
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			return;
		}
	}

	static unhideWidget(id){
		let f = "unhideWidget("+id+")";
		try{
			let w = document.getElementById(id);
			if (w == null){
				console.error(f+": element with ID \""+id+"\" not found");
			}else{
				setElementOpacity(w, 1);
			}
		}catch(x){
			console.error(f+" exception: \""+x.toString()+"\"");
			return;
		}
	}
}
