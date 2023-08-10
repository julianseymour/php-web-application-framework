<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\StaticHumanReadableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\language\ProperNoun;

class NameDatum extends TextDatum implements StaticHumanReadableNameInterface{

	public final function __construct($name = null){
		if (empty($name)) {
			$name = "name";
		}
		parent::__construct($name);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"translatable"
		]);
	}

	public static function getColumnNameStatic(){
		return "name";
	}

	public function isTranslatable(){
		return $this->getFlag("translatable");
	}

	public function setTranslatable($value){
		return $this->setFlag("translatable", $value);
	}

	public static function normalize(string $name): string{
		return preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
	}

	public static function getHumanReadableNameStatic(?StaticHumanReadableNameInterface $that = null){
		return _("Name");
	}
	
	public function getHumanReadableValue(){
		return new ProperNoun($this->getValue());
	}
	
	public function setValue($v){
		$f = __METHOD__;
		if ($v instanceof ProperNoun) {
			return parent::setValue($v->__toString());
		}
		return parent::setValue($v);
	}
}
