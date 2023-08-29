<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;


use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

trait TemplateKeyColumnTrait
{

	public function hasTemplateKey(): bool
	{
		return $this->hasColumnValue("templateKey");
	}

	public function setTemplateKey(string $value): string
	{
		return $this->setColumnValue("templateKey", $value);
	}

	public function ejectTemplateKey()
	{
		return $this->ejectColumnValue("templateKey");
	}

	public function getTemplateKey(): string
	{
		$f = __METHOD__;
		if (! $this->hasTemplateKey()) {
			Debug::error("{$f} template key is undefined");
		}
		return $this->getColumnValue("templateKey");
	}

	public function hasTemplateData(): bool
	{
		return $this->hasForeignDataStructure("templateKey");
	}

	public function setTemplateData(DataStructure $struct): DataStructure
	{
		return $this->setForeignDataStructure("templateKey", $struct);
	}

	public function getTemplateData(): DataStructure
	{
		$f = __METHOD__;
		if (! $this->hasTemplateData()) {
			Debug::error("{$f} template data is undefined");
		}
		return $this->getForeignDataStructure("templateKey");
	}

	public function ejectTemplateData(): ?DataStructure
	{
		return $this->ejectForeignDataStructure("templateKey");
	}
}
