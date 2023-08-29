<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait LabelGeneratorTrait
{

	use ArrayPropertyTrait;

	public function hasLabelStyleProperties(): bool
	{
		return $this->hasArrayProperty("labelStyle");
	}

	public function getLabelStyleProperties(): array
	{
		return $this->getProperty("labelStyle");
	}

	public function setLabelStyleProperty($key, $value)
	{
		return $this->setArrayPropertyValue("labelStyle", $key, $value);
	}

	public function setLabelClassAttribute(...$attr)
	{
		if (count($attr) == 1 && is_array($attr[0])) {
			$attr = $attr[0];
		}
		return $this->setArrayProperty("labelClassAttribute", $attr);
	}

	public function hasLabelClassAttribute()
	{
		return $this->hasArrayProperty("labelClassAttribute");
	}

	public function getLabelClassAttribute()
	{
		return $this->getProperty("labelClassAttribute");
	}
}
