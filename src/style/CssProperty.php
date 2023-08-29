<?php
namespace JulianSeymour\PHPWebApplicationFramework\style;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\ValuedElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class CssProperty extends ValuedElement implements ArrayKeyProviderInterface
{

	use NamedTrait;

	protected $propertyValue;

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"important"
		]);
	}

	public function getArrayKey(int $count)
	{
		return $this->getName();
	}

	public function hasPropertyName()
	{
		Debug::printStackTraceNoExit(ErrorMessage::getResultMessage(ERROR_DEPRECATED));
		return $this->hasName();
	}

	public function setPropertyName($name)
	{
		Debug::printStackTraceNoExit(ErrorMessage::getResultMessage(ERROR_DEPRECATED));
		return $this->setName($name);
	}

	public function __construct($name = null, $value = null)
	{
		$this->setPropertyValue(null);
		parent::__construct();
		if (isset($name) && $name != "") {
			$this->setName($name);
		} /*
		   * else{
		   * $this->setPropertyName(null);
		   * }
		   */
		if (isset($value) && $value != "") {
			$this->setPropertyValue($value);
		}
	}

	public function setPropertyValue($value)
	{
		return $this->propertyValue = $value;
	}

	public function hasPropertyValue()
	{
		return $this->propertyValue !== null;
	}

	public function getPropertyValue()
	{
		$f = __METHOD__; //CssProperty::getShortClass()."(".static::getShortClass().")->getPropertyValue()";
		if (! $this->hasPropertyValue()) {
			return null;
			Debug::error("{$f} property value is undefined");
		}
		return $this->propertyValue;
	}

	public function setValueAttribute($value)
	{
		$f = __METHOD__; //CssProperty::getShortClass()."(".static::getShortClass().")->setValueAttribute()";
		Debug::error("{$f} because of templates, this function can no longer be used; call setPropertyValue instead");
	}

	public function getPropertyName()
	{
		Debug::printStackTraceNoExit(ErrorMessage::getResultMessage(ERROR_DEPRECATED));
		return $this->getName();
	}

	public static function getElementTagStatic(): string
	{
		$f = __METHOD__; //CssProperty::getShortClass()."(".static::getShortClass().")::getElementTagStatic()";
		ErrorMessage::unimplemented($f);
	}

	public function getValueAttribute()
	{
		$f = __METHOD__; //CssProperty::getShortClass()."(".static::getShortClass().")->getValueAttribute()";
		Debug::error("{$f} because of templates, this function has been deprecated; call getPropertyValue instead");
	}

	public function setImportant($important = true)
	{
		return $this->setFlag("important", $important); // `= boolval($important);
	}

	public function isImportant()
	{
		return $this->getFlag("important"); // === true;
	}

	public function getValueString()
	{
		$string = $this->getPropertyValue();
		if ($this->isImportant()) {
			$string .= " !important";
		}
		return $string;
	}

	public function echo(bool $destroy = false): void
	{ // __toString(){
		$f = __METHOD__; //CssProperty::getShortClass()."(".static::getShortClass().")->echo()";
		echo $this->getName();
		echo ":";
		echo $this->getValueString();
		echo ";";
		if ($destroy) {
			$this->dispose();
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		// unset($this->important);
		unset($this->name);
		unset($this->propertyValue);
	}
}
