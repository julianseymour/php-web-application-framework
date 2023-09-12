<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use Exception;

abstract class ConfirmationCodeForm extends AjaxForm{

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$x1i = new HiddenInput($mode);
			$x1i->setNameAttribute("blob_64");
			$blob_64 = getInputParameter("blob_64", app()->getUseCase());
			$x1i->setValueAttribute(rawurlencode($blob_64));
			$inputs = parent::getAdHocInputs();
			$inputs[$x1i->getNameAttribute()] = $x1i;
			return $inputs;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
