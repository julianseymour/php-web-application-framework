<?php

namespace JulianSeymour\PHPWebApplicationFramework\session;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\FancyCheckbox;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;
use Exception;

abstract class AbstractSessionForm extends ExpandingMenuNestedForm{

	protected abstract function getIpAddressHint();

	protected abstract function getUserAgentHint();

	protected abstract function getFormHeaderString();

	public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
		parent::__construct($mode, $context);
		$this->setStyleProperties([
			"padding" => "1rem",
			"white-space" => "normal"
		]);
	}
	
	public static function getActionAttributeStatic(): ?string{
		return "/settings";
	}

	public function getFormDataIndices(): ?array{
		return [
			"bindIpAddress" => FancyCheckbox::class,
			"bindUserAgent" => FancyCheckbox::class
		];
	}

	public function generateFormHeader(): void{
		$div = new DivElement($this->getAllocationMode());
		$div->setInnerHTML($this->getFormHeaderString());
		$this->appendChild($div);
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public static function getMaxHeightRequirement(){
		return "999px";
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$print = false;
			$mode = $this->getAllocationMode();
			$vn = $input->getColumnName();
			switch($vn){
				case "bindIpAddress":
					$bindme = _("IP address");
					$hint = $this->getIpAddressHint();
					break;
				case "bindUserAgent":
					$bindme = _("User agent");
					$hint = $this->getUserAgentHint();
					break;
				default:
					return parent::reconfigureInput($input);
			}
			$div = new DivElement($mode);
			$div->setStyleProperties([
				"margin-top" => "1rem"
			]);
			// $div->addClassAttribute("thumbsize");
			$input->setWrapperElement($div);
			$input->setStyleProperties([
				"vertical-align" => "top"
			]);
			$span = new SpanElement($mode);
			$span->setStyleProperties([
				"max-width" => "calc(100% - 50px)",
				"display" => "inline-block"
			]);
			$span->addClassAttribute("title_description");
			$title = new DivElement($mode);
			$title->setInnerHTML(substitute(_("Bind %1%"), $bindme));
			$description = new DivElement($mode);
			$description->setInnerHTML($hint);
			$description->addClassAttribute("hint");
			$span->appendChild($title, $description);
			$input->setLabelString($span);
			// $key = $this->getContext()->getUserKey();
			$input->setIdAttribute($this->getIdAttribute() . "-" . $input->getNameAttribute());
			$label = $input->getLabel(); // must be called otherwise it will not have a label in time for its getSuccessors call
			if(!$label->hasAttributes()){
				Debug::error("{$f} label has no attributes");
			}elseif($print){
				// Debug::print("{$f} generated label does indeed have attributes");
			}
			return parent::reconfigureInput($input);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
