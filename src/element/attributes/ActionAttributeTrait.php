<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait ActionAttributeTrait
{

	public function hasActionAttribute()
	{
		return $this->hasAttribute("action");
	}

	public function getActionAttribute(){
		$f = __METHOD__; //"ActionAttributeTrait(".static::getShortClass().")->getActionAttribute()";
		try{
			if(!$this->hasActionAttribute()) {
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} action attribute is undefined; declared {$decl}");
			}
			return $this->getAttribute("action");
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setActionAttribute($action)
	{
		$f = __METHOD__; //"ActionAttributeTrait(".static::getShortClass().")->setActionAttribute()";
		if(! isset($action) || empty($action)) {
			$this->removeAttribute("action");
			return null;
		}
		/*
		 * elseif(ends_with($action, '/') && strlen($action) > 1){
		 * Debug::error("{$f} action attribute ends with a /");
		 * }
		 */
		return $this->setAttribute("action", $action);
	}
}