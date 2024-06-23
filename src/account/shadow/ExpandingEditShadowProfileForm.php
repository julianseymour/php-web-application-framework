<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\shadow;

use JulianSeymour\PHPWebApplicationFramework\form\ExpandingEditForm;

class ExpandingEditShadowProfileForm extends ExpandingEditForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$this->setTriggerInputType("checkbox");
		parent::__construct($mode, $context);
		$this->setActionAttribute(ShadowProfileForm::getActionAttributeStatic());
	}

	public function hasFormClass():bool{
		return true;
	}

	public function getFormClass():string{
		return ShadowProfileForm::class;
	}

	public static function isTemplateworthy(): bool{
		return true;
	}
}
