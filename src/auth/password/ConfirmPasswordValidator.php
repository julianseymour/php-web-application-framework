<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

class ConfirmPasswordValidator extends Validator
{

	protected $counterpartName;

	public function __construct($counterpartName)
	{
		parent::__construct();
		$this->setCounterpartNameAttribute($counterpartName);
	}

	public function setCounterpartNameAttribute($counterpartName)
	{
		return $this->counterpartName = $counterpartName;
	}

	public function hasCounterpartNameAttribute()
	{
		return isset($this->counterpartName);
	}

	public function getCounterpartNameAttribute()
	{
		$f = __METHOD__; //ConfirmPasswordValidator::getShortClass()."(".static::getShortClass().")->getCounterpartNameAttribute()";
		if(!$this->hasCounterpartNameAttribute()) {
			Debug::error("{$f} counterpart name attribute is undefined");
		}
		return $this->counterpartName;
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //ConfirmPasswordValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		if(!$this->hasInput()) {
			Debug::error("{$f} input is undefined");
		}
		$input = $this->getInput();
		if(!$input->hasNameAttribute()) {
			Debug::error("{$f} input lacks a name attribute");
		}
		$name = $input->getNameAttribute();
		$counterpartName = $this->getCounterpartNameAttribute();
		if(! array_key_exists($name, $validate_me)) {
			Debug::error("{$f} name was not posted");
			return $this->getSpecialFailureStatus();
		}elseif(! array_key_exists($counterpartName, $validate_me)) {
			Debug::error("{$f} counterpart name was not posted");
			return $this->getSpecialFailureStatus();
		}elseif($validate_me[$name] !== $validate_me[$counterpartName]) {
			Debug::print("{$f} match failed");
			return $this->getSpecialFailureStatus();
		}elseif($print) {
			Debug::print("{$f} match successful");
		}
		return $this->setObjectStatus(SUCCESS);
	}
}
