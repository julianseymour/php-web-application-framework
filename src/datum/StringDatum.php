<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\bbcode_parse_extended;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\regex_js;
use function JulianSeymour\PHPWebApplicationFramework\strip_nonalphanumeric;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class StringDatum extends Datum{

	protected $case;

	protected $minimumLength;

	protected $maximumLength;

	protected $regularExpression;

	public function dispose(): void{
		parent::dispose();
		unset($this->case);
		unset($this->minimumLength);
		unset($this->maximumLength);
		unset($this->regularExpression);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"alphanumeric",
			"bbcode",
			"nl2br"
		]);
	}

	public function setAlphanumeric(bool $value=true):bool{
		return $this->setFlag("alphanumeric", true);
	}

	public function isAlphanumeric():bool{
		return $this->getFlag('alphanumeric');
	}

	public function getDefaultValueString():string{
		return "'" . escape_quotes($this->getDefaultValue(), QUOTE_STYLE_SINGLE) . "'";
	}

	public function setNl2brFlag(bool $value = true): bool{
		return $this->setFlag("nl2br");
	}

	public function getNl2brFlag(): bool{
		return $this->getFlag("nl2br");
	}

	//XXX TODO it is not appropriate to do these transformations inside this class
	public function getHumanReadableValue(){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($this->getNeverLeaveServer()) {
			return null;
		}
		$value = $this->getValue();
		if($print) {
			Debug::print("{$f} value is \"{$value}\"");
		}
		$value = htmlspecialchars($value);
		if($print) {
			Debug::print("{$f} HTML entitied value is \"{$value}\"");
		}
		if($this->getNl2brFlag()) {
			$value = nl2br($value);
			if($print) {
				Debug::print("{$f} nl2br'd value is \"{$value}\"");
			}
		}
		if($this->getBBCodeFlag()) {
			$value = bbcode_parse_extended(null, $value);
			if($print) {
				Debug::print("{$f} bbcode parsed value is \"{$value}\"");
			}
		}
		return $value;
	}

	public function setFlag(string $name, bool $value = true): bool{
		if($value === true && $name === "bbcode") {
			$this->setNl2brFlag($value);
		}
		return parent::setFlag($name, $value);
	}

	public function setBBCodeFlag(bool $value = true): bool{
		return $this->setFlag("bbcode", $value);
	}

	public function getBBCodeFlag(): bool{
		return $this->getFlag("bbcode");
	}

	public function setCase($case){
		$f = __METHOD__; 
		if($case == null) {
			unset($this->case);
			return null;
		}elseif(!is_int($case)) {
			Debug::error("{$f} case is undefined");
		}
		switch ($case) {
			case CASE_CAMEL:
			case CASE_KEBAB:
			case CASE_LOWER:
			case CASE_PASCAL:
			case CASE_SNAKE:
			case CASE_TITLE:
			case CASE_UPPER:
				break;
			default:
				Debug::error("{$f} invalid case \"{$case}\"");
		}
		return $this->case = $case;
	}

	public function hasCase():bool{
		return isset($this->case) && is_int($this->case);
	}

	public function getCase(){
		$f = __METHOD__;
		if(!$this->hasCase()) {
			Debug::error("{$f} case is undefined");
		}
		return $this->case;
	}

	public function cast($v){
		$f = __METHOD__;
		if($v instanceof ValueReturningCommandInterface) {
			while ($v instanceof ValueReturningCommandInterface) {
				$v = $v->evaluate();
			}
		}
		if($this->isAlphanumeric()) {
			$v = strip_nonalphanumeric($v);
		}
		if($this->hasCase()) {
			switch ($this->getCase()) {
				case CASE_LOWER:
					return strtolower($v);
				case CASE_UPPER:
					return strtoupper($v);
				default:
					Debug::print("{$f} only upper and lower case string transformations are currently supported");
			}
		}
		return $v;
	}

	public function setRequiredLength(?int $l):?int{
		$f = __METHOD__;
		if(!is_int($l)) {
			Debug::error("{$f} length is not an integer");
		}elseif($l < 1) {
			Debug::error("{$f} length is not positive");
		}
		return $this->setMaximumLength($this->setMinimumLength($l));
	}

	public function hasRequiredLength():bool{
		return $this->hasMinimumLength() && $this->hasMaximumLength() && $this->getMinimumLength() === $this->getMaximumLength();
	}

	public function getRequiredLength():int{
		$f = __METHOD__;
		if(!$this->hasRequiredLength()) {
			Debug::error("{$f} required length is undefined");
		}
		return $this->getMinimumLength();
	}

	public function setRegularExpression($regex){
		return $this->regularExpression = $regex;
	}

	public function hasRegularExpression():bool{
		return isset($this->regularExpression);
	}

	public function getJavaScriptRegularExpression(){
		return regex_js($this->getRegularExpression());
	}

	public function getRegularExpression(){
		$f = __METHOD__; //StringDatum::getShortClass()."(".static::getShortClass().")->getRegularExpression()";
		if(!$this->hasRegularExpression()) {
			Debug::error("{$f} regular expression is undefined");
		}
		return $this->regularExpression;
	}

	public function setMinimumLength(?int $l):?int{
		$f = __METHOD__;
		if(!is_int($l)) {
			Debug::error("{$f} length is not an integer");
		}elseif($l < 1) {
			Debug::error("{$f} length is not positive");
		}
		return $this->minimumLength = $l;
	}

	public function hasMinimumLength():bool{
		return isset($this->minimumLength);
	}

	public function getMinimumLength():int{
		$f = __METHOD__;
		if(!$this->hasMinimumLength()) {
			Debug::error("{$f} minimum length is undefined");
		}
		return $this->minimumLength;
	}

	public function setMaximumLength(?int $l):?int{
		$f = __METHOD__;
		if(!is_int($l)) {
			Debug::error("{$f} length is not an integer");
		}elseif($l < 1) {
			Debug::error("{$f} length is not positive");
		}
		return $this->maximumLength = $l;
	}

	public function hasMaximumLength():bool{
		return isset($this->maximumLength);
	}

	public function getMaximumLength():int{
		$f = __METHOD__;
		if(!$this->hasMaximumLength()) {
			$index = $this->getName();
			Debug::error("{$f} maximum length is undefined for datum \"{$index}\"");
		}
		return $this->maximumLength;
	}

	public function validate($v): int{
		$f = __METHOD__;
		$print = false;
		$length = strlen($v);
		if($this->hasRequiredLength() && $length !== $this->getRequiredLength()) {
			if($print){
				Debug::error("{$f} invalid string \"{$v}\" length {$length}");
			}
			return FAILURE;
		}elseif($this->hasMinimumLength() && $length < $this->getMinimumLength()) {
			if($print){
				Debug::error("{$f} string \"{$v}\" is too short at length {$length}");
			}
			return FAILURE;
		}elseif($this->hasMaximumLength() && $length > $this->getMaximumLength()) {
			if($print){
				Debug::error("{$f} string \"{$v}\" is too long at length {$length}");
			}
			return FAILURE;
		}elseif($this->hasRegularExpression() && ! preg_match($this->getRegularExpression(), $v)) {
			if($print){
				Debug::error("{$f} string \"{$v}\" failed matching regular expression");
			}
			return FAILURE;
		}
		return parent::validate($v);
	}

	public static function parseString(string $v){
		return $v;
	}

	public static function getTypeSpecifier():string{
		return 's';
	}

	public function parseValueFromQueryResult($raw){
		return $raw;
	}

	public static function validateStatic($value): int{
		return SUCCESS;
	}

	public function parseValueFromSuperglobalArray($value){
		return $value;
	}
}

