<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnDefiningTrait;
use Exception;

class DatumValidator extends Validator{
	
	use ColumnDefiningTrait;
	use ValuedTrait;
	
	public function __construct(Datum $datum, $value){
		parent::__construct();
		$this->setColumnDefinition($datum);
		$this->setValue($value);
	}
	
	public function evaluate(&$validate_me):int{
		$f = __METHOD__; //DatumValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		try{
			$datum = $this->getColumnDefinition();
			$value = $this->getValue();
			if($datum->validate($value)){
				return SUCCESS;
			}
			return $this->getFailureStatus();
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function dispose():void{
		parent::dispose();
		unset($this->columnDefinition);
		unset($this->value);
	}
}
