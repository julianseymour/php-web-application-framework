<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class ToggleInput extends CheckboxInput{

	use StyleSheetPathTrait;
	
	protected $labelWrapper;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("hidden");
	}

	public function bindContext($context){
		$f = __METHOD__;
		try{
			$value = $context->getValue();
			if($value) {
				$this->addClassAttribute("init_on");
			}else{
				$this->addClassAttribute("init_off");
			}
			return parent::bindContext($context);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasLabelWrapper(){
		return isset($this->labelWrapper);
	}

	public function configure(AjaxForm $form): int{
		$ret = parent::configure($form);
		$mode = $this->getAllocationMode();
		$label = new LabelElement($mode);
		$label->setForAttribute($this->getIdAttribute());
		$label->addClassAttribute("toggle_input_label");
		$span1 = new SpanElement($mode);
		$span1->setInnerHTML($this->getLabelString());
		$span2 = new SpanElement($mode);
		$span2->addClassAttribute("toggle_switch");
		$span2->addClassAttribute("toggle_switch_from_gp");
		$span2->addClassAttribute("inline-block");
		$span2->setInnerHTML("&nbsp");
		$label->appendChild($span2);
		$this->pushSuccessor($label);
		if(!$this->hasWrapperElement()) {
			$mcns_m = new DivElement($mode);
			$mcns_m->addClassAttribute("mncns_m");
			$this->setWrapperElement($mcns_m);
		}
		return SUCCESS;
	}
}
