<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class AttributeSelector extends Selector
{

	protected $attributeName;

	protected $attributeValue;

	public function setAttributeName($attribute)
	{
		return $this->attributeName = $attribute;
	}

	public function getAttributeName()
	{
		return $this->attributeName;
	}

	public function setAttributeValue($value)
	{
		return $this->attributeValue = $value;
	}

	public function getAttributeValue()
	{
		return $this->attributeValue;
	}

	public function hasAttributeValue()
	{
		return isset($this->attributeValue) && $this->attributeValue !== "";
	}

	public static function echoQuotes()
	{
		echo "\"";
	}

	public function echoAttributeValue()
	{
		$this->echoQuotes();
		echo $this->getAttributeValue();
		$this->echoQuotes();
	}

	public static function echoOperator()
	{
		echo "=";
	}

	public function echo(bool $destroy = false): void
	{
		echo "[";
		echo $this->getAttributeName();
		if ($this->hasAttributeValue()) {
			static::echoOperator();
			$this->echoAttributeValue();
		}
		echo "]";
	}

	public function __construct($name = null, $value = null)
	{
		parent::__construct();
		if (isset($name)) {
			$this->setAttributeName($name);
			if (isset($value)) {
				$this->setAttributeValue($value);
			}
		}
	}
}
