<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

class CreateStoredRoutinesForm extends AjaxForm{
	
	public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("create_stored_routines_form");
	}
	
	public function getDirectives():?array{
		return [DIRECTIVE_SUBMIT];
	}
	
	public function generateButtons(string $directive):?array{
		$f = __METHOD__;
		switch($directive){
			case DIRECTIVE_SUBMIT:
				$button = $this->generateGenericButton($directive);
				$button->setInnerHTML(_("Create stored routines"));
				return [$button];
			default:
				Debug::error("{$f} invalid directive \"{$directive}\"");
		}
	}
	
	public static function getActionAttributeStatic():?string{
		return "/create_routines";
	}
	
	public static function getMethodAttributeStatic():?string{
		return HTTP_REQUEST_METHOD_POST;
	}
	
	public function getFormDataIndices():?array{
		return null;
	}
}
