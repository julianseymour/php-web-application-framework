<?php

namespace JulianSeymour\PHPWebApplicationFramework\template;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait TemplateFlagTrait{

	use FlagBearingTrait;

	public function getTemplateFlag(): bool{
		return $this->getFlag("template");
	}

	public function setTemplateFlag(bool $flag = true): bool{
		return $this->setFlag("template", $flag);
	}

	// XXX TODO there is a similarly named function for TemplateContextInterface that does something unrelated
	public function template(bool $value = true):object{
		$this->setTemplateFlag($value);
		return $this;
	}
}
