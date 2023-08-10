class SearchUseCase extends UseCase{
	
	static handleResponse(response){
		let f = "handleResponse()";
		try{
			let print = false;
			if(print){
				//window.alert(f.concat(": entered"));
			}
			switch(response.status){
				case SUCCESS:
					if(print){
						console.log(f.concat(": Search successful"));
					}
					if(!isset(response.clearButtonId)){
						return error(f.concat(": clear button ID is undefined"));
					}else if(!isset(response.searchCheckboxId)){
						return error(f.concat(": search input ID is undefined"));
					}else if(!isset(response.searchResultsId)){
						return error(f.concat(": search results ID is undefined"));
					}
					if(print){
						console.log(f.concat(": about to get clear search results button with ID ").concat(response.clearButtonId));
					}
					document.getElementById(response.clearButtonId).style['opacity'] = 1;
					checkInputWithId(response.searchCheckboxId, function(){
						if(print){
							window.alert(f.concat(": search results element ID is ").concat(response.searchResultsId));
						}
						let insert_here = document.getElementById(response.searchResultsId);
						insert_here.textContent = "";
						let dss = response.getDataStructures();
						if(dss.keys().length == 0){
							console.log(dss);
							return error(f, "0 search results! Should not have gotten here");
						}
						if(print){
							console.log(f.concat(": about to print data structures"));
							console.log(dss);
						}
						for(let key in dss){
							let ds = response.getDataStructure(key);
							if(!ds.searchResult){
								console.log(f.concat(": data structure with key \"").concat(key).concat("\" does not have its search result flag set"));
								continue;
							}else if(!isset(ds.elementClass)){
								return error(f.concat(": element class is undefined"));
							}
							let result = bindElement(ds.elementClass, ds);
							console.log(result);
							//result.style['transition-delay'] = "transform:".concat(delay).concat("s");
							insert_here.appendChild(result);
						}
					});
					break;
				default:
					console.log(f.concat(": something went wrong conducting search"));
					UseCase.handleResponse(response);
			}
			if(print){
				window.alert(f.concat(": returning normally"));
			}
		}catch(x){
			return error(f, x);
		}
	}
}