/*
	Copyright (c) 2007-2016 Kevin van Zonneveld (https://kvz.io) 
	and Contributors (https://locutus.io/authors)

	Permission is hereby granted, free of charge, to any person obtaining a copy of
	this software and associated documentation files (the "Software"), to deal in
	the Software without restriction, including without limitation the rights to
	use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
	of the Software, and to permit persons to whom the Software is furnished to do
	so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/

//https://locutus.io/php/array/array_key_exists/
function array_key_exists(key, search){
	if(!search || (search.constructor !== Array && search.constructor !== Object)){
		return false;
	}
	return key in search;
}

//discuss at: https://locutus.io/php/array_search/
function array_search(needle, haystack, argStrict){
	const strict = !!argStrict;
	let key = '';
	if(typeof needle === 'object' && needle.exec){
		// Duck-type for RegExp
		if(!strict){
			// Let's consider case sensitive searches as strict
			const flags = 'i' + (needle.global ? 'g' : '') +
				(needle.multiline ? 'm' : '') +
				// sticky is FF only
				(needle.sticky ? 'y' : '');
			needle = new RegExp(needle.source, flags);
		}
		for(key in haystack){
			if(haystack.hasOwnProperty(key)){
				if(needle.test(haystack[key])){
					return key;
				}
			}
		}
		return false;
	}
	for(key in haystack){
		if(haystack.hasOwnProperty(key)){
			if((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)){ // eslint-disable-line eqeqeq
				return key;
			}
		}
	}
	return false;
}

//https://locutus.io/php/array/array_slice/
function array_slice(arr, offst, lgth, preserveKeys){
	let key = '';
	if(Object.prototype.toString.call(arr) !== '[object Array]' || (preserveKeys && offst !== 0)){
		let lgt = 0;
		let newAssoc = {};
		for (key in arr){
			lgt += 1;
			newAssoc[key] = arr[key];
		}
		arr = newAssoc;
		offst = (offst < 0) ? lgt + offst : offst;
		lgth = lgth === undefined ? lgt : (lgth < 0) ? lgt + lgth - offst : lgth;
		let assoc = {};
		let start = false;
		let it = -1;
		let arrlgth = 0;
		let noPkIdx = 0;
		for(key in arr){
			++it;
			if(arrlgth >= lgth){
				break;
			}
			if(it === offst){
				start = true;
			}
			if(!start){
				continue;
			}
			++arrlgth;
			if(is_int(key) && !preserveKeys){
				assoc[noPkIdx++] = arr[key];
			}else{
				assoc[key] = arr[key];
			}
		}
		return assoc;
	}else if(lgth === undefined){
		return arr.slice(offst);
	}else if(lgth >= 0){
		return arr.slice(offst, offst + lgth);
	}
	return arr.slice(offst, lgth);
}

//https://locutus.io/php/var/empty/
//seems that something has changed in Firefox during development -- hasOwnProperty now returns false for all properties of an uploaded File object
function empty(value){
	let f = "empty()";
	let print = false;
	let undef;
	let emptyValues = [undef, null, false, 0, '']; //, '0'];
	if(typeof value === 'object'){
		for(let key in value){
			if(isset(value[key])){//value.hasOwnProperty(key)){
				if(print){
					console.log(f.concat(": object has a property with key \"").concat(key).concat("\"; returning false"));
				}
				return false;
			}else if(print){
				console.log(f.concat(": object has no property with key \"").concat(key).concat("\""));
			}
		}
		if(print){
			console.log(f+": object has no properties; returning true");
		}
		return true;
	}
	let len = emptyValues.length;
	for(let i = 0; i < len; i++){
		if(value === emptyValues[i]){
			//console.log(f+": about to log the empty value");
			//console.log(value);
			if(print){
				let err = f.concat(": value \"").concat(value).concat("\" is equivalent to \"").concat(emptyValues[i]).concat("\"; returning true");
				console.log(err);
			}
			return true;
		}
	}
	if(print){
		console.log(f+": returning false");
	}
	return false;
}

//https://locutus.io/php/url/http_build_query/
function http_build_query(formdata, numericPrefix, argSeparator, encType){
	let f = "http_build_query()";
	try{
		let print = false;
		let encodeFunc;
		switch(encType){
			case 'PHP_QUERY_RFC3986':
				encodeFunc = rawurlencode;
				break;
			case 'PHP_QUERY_RFC1738':
			default:
				encodeFunc = urlencode;
				break;
		}
		let value;
		let key;
		const tmp = [];
		let _httpBuildQueryHelper = function(key, val, argSeparator){
			let k;
			const tmp = [];
			if(val === true){
				val = '1';
			}else if(val === false){
				val = '0';
			}
			if(val !== null){
				if(typeof val === 'object'){
					for(k in val){
						if(val[k] !== null){
							tmp.push(_httpBuildQueryHelper(key + '[' + k + ']', val[k], argSeparator));
						}
					}
					return tmp.join(argSeparator);
				}else if(typeof val !== 'function'){
					return encodeFunc(key) + '=' + encodeFunc(val)
				}else if(print){
					console.log(f.concat(": error value is \"").concat(val).concat("\""));
				}
				throw new Error('There was an error processing for http_build_query()');
			}
			return '';
		}
		if(!argSeparator){
			argSeparator = '&';
		}
		for(key in formdata){
			value = formdata[key];
			if(numericPrefix && !isNaN(key)){
				key = String(numericPrefix) + key;
			}
			const query = _httpBuildQueryHelper(key, value, argSeparator);
			if(query !== ''){
				tmp.push(query);
			}
		}
		return tmp.join(argSeparator);
	}catch(x){
		return error(f, x);
	}
}

//https://locutus.io/php/var/is_int/
function is_int(mixedVar){
	return mixedVar === +mixedVar && isFinite(mixedVar) && !(mixedVar % 1)
}

//XXX inform locutus that isset($zero) in php returns true
function isset(val){
	let f = "isset()";
	let print = false;
	if(print){
		if(typeof val == 'undefined'){
			console.log(f+": value is undefined");
		}
		if(val == null){
			console.log(f+": value is null");
		}
	}
	return typeof val != 'undefined' && val != null; //&& val != 0; 
}

//https://locutus.io/php/strings/nl2br/
function nl2br(str, is_xhtml){
	if(typeof str === 'undefined' || str === null){
		return '';
	}
	let breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

//https://locutus.io/php/url/rawurldecode/
function rawurldecode(str){
	return decodeURIComponent((str + '').replace(/%(?![\da-f]{2})/gi, function(){
		return '%25';
	}));
}

//https://locutus.io/php/url/rawurlencode/
function rawurlencode(str){
	str = (str + '');
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28')
		.replace(/\)/g, '%29').replace(/\*/g, '%2A');
}

//https://locutus.io/php/strings/str_replace/
function str_replace(search, replace, subject, countObj){
	let i = 0;
	let j = 0;
	let temp = '';
	let repl = '';
	let sl = 0;
	let fl = 0;
	let f = [].concat(search);
	let r = [].concat(replace);
	let s = subject;
	let ra = Object.prototype.toString.call(r) === '[object Array]';
	let sa = Object.prototype.toString.call(s) === '[object Array]';
	s = [].concat(s);
	if(typeof search === 'object' && typeof replace === 'string'){
		temp = replace;
		replace = [];
		for(i = 0; i < search.length; i += 1){
			replace[i] = temp;
		}
		temp = '';
		r = [].concat(replace);
		ra = Object.prototype.toString.call(r) === '[object Array]';
	}
	if(typeof countObj !== 'undefined'){
		countObj.value = 0;
	}
	for(i = 0, sl = s.length; i < sl; i++){
		if(s[i] === ''){
			continue
		}
		for(j = 0, fl = f.length; j < fl; j++){
			temp = s[i] + '';
			repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
			s[i] = (temp).split(f[j]).join(repl);
			if(typeof countObj !== 'undefined'){
				countObj.value += ((temp.split(f[j])).length - 1);
			}
		}
	}
	return sa ? s : s[0];
}

//https://locutus.io/php/url/urldecode/
function urldecode(str){
	return decodeURIComponent((str + '').replace(/%(?![\da-f]{2})/gi, function(){
		return '%25'
	}).replace(/\+/g, '%20'));
}

//https://locutus.io/php/url/urlencode/
function urlencode(str){
	str = (str + '');
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28')
		.replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/~/g, '%7E').replace(/%20/g, '+');
}

//https://locutus.io/php/var/is_array/
function is_array (mixedVar){ // eslint-disable-line camelcase
	  //  discuss at: https://locutus.io/php/is_array/
	  // original by: Kevin van Zonneveld (https://kvz.io)
	  // improved by: Legaev Andrey
	  // improved by: Onno Marsman (https://twitter.com/onnomarsman)
	  // improved by: Brett Zamir (https://brett-zamir.me)
	  // improved by: Nathan Sepulveda
	  // improved by: Brett Zamir (https://brett-zamir.me)
	  // bugfixed by: Cord
	  // bugfixed by: Manish
	  // bugfixed by: Brett Zamir (https://brett-zamir.me)
	  //      note 1: In Locutus, javascript objects are like php associative arrays,
	  //      note 1: thus JavaScript objects will also
	  //      note 1: return true in this function (except for objects which inherit properties,
	  //      note 1: being thus used as objects),
	  //      note 1: unless you do ini_set('locutus.objectsAsArrays', 0),
	  //      note 1: in which case only genuine JavaScript arrays
	  //      note 1: will return true
	  //   example 1: is_array(['Kevin', 'van', 'Zonneveld'])
	  //   returns 1: true
	  //   example 2: is_array('Kevin van Zonneveld')
	  //   returns 2: false
	  //   example 3: is_array({0: 'Kevin', 1: 'van', 2: 'Zonneveld'})
	  //   returns 3: true
	  //   example 4: ini_set('locutus.objectsAsArrays', 0)
	  //   example 4: is_array({0: 'Kevin', 1: 'van', 2: 'Zonneveld'})
	  //   returns 4: false
	  //   example 5: is_array(function tmp_a (){ this.name = 'Kevin' })
	  //   returns 5: false
	  const _getFuncName = function (fn){
	    const name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
	    if(!name){
	      return '(Anonymous)'
	    }
	    return name[1]
	  }
	  const _isArray = function (mixedVar){
	    // return Object.prototype.toString.call(mixedVar) === '[object Array]';
	    // The above works, but let's do the even more stringent approach:
	    // (since Object.prototype.toString could be overridden)
	    // Null, Not an object, no length property so couldn't be an Array (or String)
	    if(!mixedVar || typeof mixedVar !== 'object' || typeof mixedVar.length !== 'number'){
	      return false
	    }
	    const len = mixedVar.length
	    mixedVar[mixedVar.length] = 'bogus'
	    // The only way I can think of to get around this (or where there would be trouble)
	    // would be to have an object defined
	    // with a custom "length" getter which changed behavior on each call
	    // (or a setter to mess up the following below) or a custom
	    // setter for numeric properties, but even that would need to listen for
	    // specific indexes; but there should be no false negatives
	    // and such a false positive would need to rely on later JavaScript
	    // innovations like __defineSetter__
	    if(len !== mixedVar.length){
	      // We know it's an array since length auto-changed with the addition of a
	      // numeric property at its length end, so safely get rid of our bogus element
	      mixedVar.length -= 1
	      return true
	    }
	    // Get rid of the property we added onto a non-array object; only possible
	    // side-effect is if the user adds back the property later, it will iterate
	    // this property in the older order placement in IE (an order which should not
	    // be depended on anyways)
	    delete mixedVar[mixedVar.length]
	    return false
	  }
	  if(!mixedVar || typeof mixedVar !== 'object'){
	    return false
	  }
	  const isArray = _isArray(mixedVar)
	  if(isArray){
	    return true
	  }
	  const iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.objectsAsArrays') : undefined) || 'on'
	  if(iniVal === 'on'){
	    const asString = Object.prototype.toString.call(mixedVar)
	    const asFunc = _getFuncName(mixedVar.constructor)
	    if(asString === '[object Object]' && asFunc === 'Object'){
	      // Most likely a literal and intended as assoc. array
	      return true
	    }
	  }
	  return false
	}
