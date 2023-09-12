<?php
namespace JulianSeymour\PHPWebApplicationFramework\paginate;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;
use Exception;

class JumpToPageForm extends AjaxForm{

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$span = new SpanElement($this->getAllocationMode());
			$span->setInnerHTML(_("Jump to page"));
			$this->appendChild($span);
			return [
				$span
			];
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getFormInputManifest(): ?array{
		return $this->getFormDataIndices();
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_GET;
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_SUBMIT
		];
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_SUBMIT:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Go to page");
				$button->setInnerHTML($innerHTML);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$jump = new NumberInput($mode);
			$jump->setNameAttribute("jumpToPage");
			$jump->setMinimumAttribute(0);
			$jump->setMaximumAttribute($context->getLastPage());
			$lim = $context->getLimitPerPage();
			$limit = new HiddenInput($mode);
			$limit->setNameAttribute("limit");
			$limit->setValueAttribute($lim);
			$inputs = parent::getAdHocInputs();
			foreach([
				$jump,
				$limit
			] as $input) {
				$inputs[$input->getNameAttribute()] = $input;
			}
			return $inputs;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setActionAttribute(request()->getRequestURI());
	}

	public function skipFormInitialization(): bool{
		return false;
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "jump_to_page";
	}

	public static function getActionAttributeStatic(): ?string{
		return null;
	}
}