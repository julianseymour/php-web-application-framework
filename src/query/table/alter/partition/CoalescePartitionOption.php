<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class CoalescePartitionOption extends AlterOption
{

	protected $number;

	public function __construct($number)
	{
		parent::__construct();
		$this->setNumber($number);
	}

	public function setNumber($number)
	{
		$f = __METHOD__; //CoalescePartitionOption::getShortClass()."(".static::getShortClass().")->setNumber()";
		if($number === null){
			unset($this->number);
			return null;
		}elseif(!is_int($number)){
			Debug::error("{$f} this function accepts only integers");
		}
		return $this->number = $number;
	}

	public function hasNumber()
	{
		return isset($this->number) && is_int($this->number);
	}

	public function getNumber()
	{
		$f = __METHOD__; //CoalescePartitionOption::getShortClass()."(".static::getShortClass().")->getNumber()";
		if(!$this->hasNumber()){
			Debug::error("{$f} partition number is undefined");
		}
		return $this->number;
	}

	public function toSQL(): string
	{
		return "coalesce partition " . $this->getNumber();
	}
}