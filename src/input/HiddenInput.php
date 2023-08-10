<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class HiddenInput extends InputElement
{

	public function getTypeAttribute(): string
	{
		return "hidden";
	}

	/*
	 * public function hasContainerElementTag(){
	 * return false;
	 * }
	 */

	/*
	 * public function isHiddenInput(){
	 * return true;
	 * }
	 */
	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_HIDDEN;
	}

	public function getAllocationMode(): int
	{
		return ALLOCATION_MODE_NEVER;
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}

	/*
	 * public function setNameAttribute($name){
	 * $f = __METHOD__; //HiddenInput::getShortClass()."(".static::getShortClass().")->setNameAttribute()";
	 * if(is_string($name) && $name === "servicesEnabled[6085741f8cf97662584d3190467ef848ea51e51f][uniqueKey]"){
	 * $old = $this->getNameAttribute();
	 * $did = $this->getDebugId();
	 * $decl = $this->getDeclarationLine();
	 * Debug::printStackTraceNoExit("{$f} entered, old name was \"{$old}\", debug ID is {$did}, instantated {$decl}");
	 * }
	 * return parent::setNameAttribute($name);
	 * }
	 */
}
