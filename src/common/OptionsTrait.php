<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * A trait for objects that have a property called $options
 * @author j
 *
 */
trait OptionsTrait{
	
	protected $options;
	
	public function hasOptions():bool{
		return isset($this->options);
	}
	
	public function setOptions($options){
		if($this->hasOptions()){
			$this->release($this->options);
		}
		return $this->options = $this->claim($options);
	}
	
	public function getOptions(){
		$f = __METHOD__;
		if(!$this->hasOptions()){
			Debug::error("{$f} options are undefined");
		}
		return $this->options;
	}
}