<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\input\choice\MultipleRadioButtons;

class RadioButtonInput extends CheckedInput
{

	// protected $mutex;

	/*
	 * public function __construct($mode=ALLOCATION_MODE_UNDEFINED, $context=null){
	 * $f = __METHOD__; //RadioButtonInput::getShortClass()."(".static::getShortClass().")->__construct()";
	 * parent::__construct($mode, $context);
	 * Debug::print("{$f} constructed radio button with debug ID \"{$this->debugId}\"");
	 * }
	 */

	/*
	 * public function dispose():void{
	 * //$f = __METHOD__; //RadioButtonInput::getShortClass()."(".static::getShortClass().")->dispose()";
	 * //Debug::printStackTraceNoExit("{$f} entered");
	 * parent::dispose();
	 * unset($this->mutex);
	 * }
	 */
	public function getSensitiveFlag(): bool
	{
		return false;
	}

	public function hasIdAttribute(): bool
	{
		return true;
	}

	/**
	 *
	 * @return MultipleRadioButtons
	 */
	/*
	 * public function getMutex(){
	 * return $this->mutex;
	 * }
	 *
	 * public function hasMutex(){
	 * return isset($this->mutex);
	 * }
	 *
	 * public function setMutex($mutex){
	 * $f = __METHOD__; //RadioButtonInput::getShortClass()."(".static::getShortClass().")->setMutex()";
	 * if($mutex == null){
	 * unset($this->mutex);
	 * return null;
	 * }elseif(!$mutex instanceof MultipleRadioButtons){
	 * Debug::error("{$f} mutex must be an instanceof MultipleRadioButtons");
	 * }
	 * return $this->mutex = $mutex;
	 * }
	 */
	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_RADIO;
	}
}
