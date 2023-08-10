<?php
namespace JulianSeymour\PHPWebApplicationFramework\paginate;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;
use Exception;

class LimitItemsPerPageForm extends AjaxForm{

	public function getDirectives(): ?array{
		return [
			"submit"
		];
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_GET;
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("limit_drops");
		$this->addClassAttribute("limit_drops");
	}

	public function generateFormHeader(): void{
		$f = __METHOD__;
		try {
			$limit_field = new SpanElement();
			$limit_field->addClassAttribute("limit_field");
			$limit_span = new SpanElement();
			$limit_span->setInnerHTML(_("Limit"));
			$limit_field->appendChild($limit_span);
			$limit = new NumberInput();
			$limit->setMinimumAttribute(1);
			$context = $this->getContext();
			$use_case = app()->getUseCase();
			$paginator = $use_case->getLoadoutGenerator()->getPaginator($use_case);
			$toc = $paginator->getTotalItemCount();
			$lim = $paginator->getLimitPerPage();
			$limit->setMaximumAttribute($toc);
			$limit->setPlaceholderAttribute(_("Items"));
			$limit->setNameAttribute("limit");
			if ($lim !== null) {
				$limit->setValueAttribute($lim);
			}
			$limit_field->appendChild($limit);
			$items_span = new SpanElement();
			$items_span->setInnerHTML(" " . _("Items"));
			$limit_field->appendChild($items_span);
			$this->appendChild($limit_field);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function skipFormInitialization(): bool{
		return false;
	}

	public static function getFormDispatchIdStatic(): ?string{
		return null; // "items_per_page";
	}

	public static function getActionAttributeStatic(): ?string{
		return request()->getRequestURI();
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see AjaxForm::skipAntiXsrfTokenInputs()
	 */
	public static function skipAntiXsrfTokenInputs(): bool{
		return true;
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_SUBMIT:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Refresh");
				$button->setInnerHTML($innerHTML);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
