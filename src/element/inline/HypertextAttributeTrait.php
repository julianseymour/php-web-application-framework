<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * attribute for elements with an href attribute
 *
 * @author j
 *        
 */
trait HypertextAttributeTrait
{

	public function setHrefAttribute($href)
	{
		return $this->setAttribute("href", $href);
	}

	public function getHrefAttribute()
	{
		$f = __METHOD__; //"HypertextAttributeTrait(".static::getShortClass().")->geHrefAttribute()";
		if(!$this->hasHrefAttribute()){
			Debug::error("{$f} href attribute is undefined");
		}
		return $this->getAttribute("href");
	}

	public function hasHrefAttribute()
	{
		return $this->hasAttribute("href");
	}
}
