<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\CompoundDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class SearchTimestampDatum extends CompoundDatum{

	use MultipleSearchClassesTrait;

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return array_merge(parent::declarePropertyTypes($that), [
			"searchClasses" => new AndCommand(DataStructure::class, "class")
		]);
	}

	public function getHumanWritableValue(){
		$f = __METHOD__;
		return null;
	}

	public function generateComponents(){
		$f = __METHOD__;
		$vn = $this->getColumnName();
		$start = TimestampDatum::generateComponent("start", $vn);
		$end = TimestampDatum::generateComponent("end", $vn);
		return [
			"start" => $start,
			"end" => $end
		];
	}

	public function getIntervalStartComponent(){
		return $this->getComponent("start");
	}

	public function getIntervalEndComponent(){
		return $this->getComponent("end");
	}

	public function getHumanReadableValue(){
		$start = $this->getIntervalStartComponent()->getHumanReadableValue();
		$end = $this->getIntervalEndComponent()->getHumanReadableValue();
		return substitute(_("%1% to %2%"), $start, $end);
	}

	public static function parseString(string $string){
		ErrorMessage::unimplemented(__METHOD__);
	}

	public static function validateStatic($value): int{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function getUrlEncodedValue(){
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function setIntervalStart($start){
		return $this->setComponentValue("start", $start);
	}

	public function getIntervalStart(){
		return $this->getComponentValue("start");
	}

	public function hasIntervalStart(){
		return $this->hasComponentValue("start");
	}

	public function getIntervalEnd(){
		return $this->getComponentValue("end");
	}

	public function hasIntervalEnd(){
		return $this->hasComponentValue("end");
	}

	public function setIntervalEnd($end){
		return $this->setComponentValue("end", $end);
	}

	public function getColumnTypeString(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}
}
