<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\StyleElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\Choice;
use JulianSeymour\PHPWebApplicationFramework\input\choice\MultipleRadioButtons;
use JulianSeymour\PHPWebApplicationFramework\style\CssRule;
use JulianSeymour\PHPWebApplicationFramework\style\selector\ElementSelector;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\release;

class TabMutex extends MultipleRadioButtons{

	protected $labelContainerId;

	protected $selectorLogic;

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->labelContainerId, $deallocate);
		$this->release($this->selectorLogic, $deallocate);
	}
	
	public function generateInput(Choice $opt): RadioButtonInput{
		$input = parent::generateInput($opt);
		$input->setAttribute("tab", $opt->getValue());
		$input->hide();
		return $input;
	}

	public function hasLabelContainerClassAttribute(): bool{
		return $this->hasArrayProperty("labelContainerClassAttribute");
	}

	public function setLabelContainerClassAttribute(...$attr){
		if(count($attr) == 1 && is_array($attr[0])){
			$attr = $attr[0];
		}
		return $this->setArrayProperty("labelContainerClassAttribute", $attr);
	}

	public function getLabelContainerClassAttribute(){
		return $this->getProperty("labelContainerClassAttribute");
	}

	public function hasLabelContainerId(): bool{
		return isset($this->labelContainerId);
	}

	public function setLabelContainerId($id){
		if($this->hasLabelContainerId()){
			$this->release($this->labelContainerId);
		}
		return $this->labelContainerId = $this->claim($id);
	}

	public function getLabelContainerId(){
		if(!$this->hasLabelContainerId()){
			return $this->setLabelContainerId(sha1(random_bytes(32)));
		}
		return $this->labelContainerId;
	}

	public function generateLabelContainer(): DivElement{
		$c = new DivElement();
		$c->setIdAttribute($this->getLabelContainerId());
		$c->addClassAttribute("tab_labels");
		if($this->hasLabelContainerClassAttribute()){
			$c->addClassAttribute(...$this->getLabelContainerClassAttribute());
		}
		$c->appendChild(...array_values($this->getLabelElements()));
		return $c;
	}

	public function generateLabelElement($choice, ?string $label_class = null): Element{
		$ret = parent::generateLabelElement($choice);
		$ret->setAttribute("tab", $choice->getValue());
		return $ret;
	}

	public function getLabelElements(){
		$f = __METHOD__;
		try{
			$arr = [];
			$choices = $this->getChoices();
			if(empty($choices)){
				Debug::error("{$f} mutex options are undefined");
			}
			if($this->getAllFlag()){
				array_unshift($choices, $this->chooseAll());
			}
			foreach($choices as $opt){
				$arr[$opt->getValue()] = $this->generateLabelElement($opt);
			}
			return $arr;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getSelectorLogic(){
		if(!$this->hasSelectorLogic()){
			return SIGNAGE_BIT_POSITIVE;
		}
		return $this->selectorLogic;
	}

	public function hasSelectorLogic(): bool{
		return isset($this->selectorLogic);
	}

	public function setSelectorLogic(?int $bit): int{
		if($this->hasSelectorLogic()){
			$this->release($this->selectorLogic);
		}
		return $this->selectorLogic = $this->claims($bit);
	}

	protected static function getPositiveInputSelector($keyword){
		return ElementSelector::element("input")->attributes([
			"type" => "radio",
			"tab" => $keyword
		])->checked();
	}

	protected static function getNegativeInputSelector($keyword){
		return ElementSelector::element("input")->attributes([
			"type" => "radio",
			"tab" => $keyword
		])->not(":checked");
	}

	protected static function getPositiveLabelSelector($keyword){
		return ElementSelector::element("label")->attribute("tab", $keyword);
	}

	// rules needed:
	// 1. when the button is clicked,
	// 1a. the contents should become visible
	// 1b. overline the tab max-width = whatever
	// 1c. change tab background color to something lighter
	// 2. when the button is not clicked,
	// 2a. contents become invisible
	// 2b. tab overline width = 0
	// 2c. tab backgorund color darkens
	protected function getPositiveLogicStyleElement(){
		$f = __METHOD__;
		$style = new StyleElement();
		$inline_block_rule = CssRule::rule()->withStyleProperties([
			"display" => "var(--display)" // "inline-block"
		]);
		$filter_rule = CssRule::rule()->withStyleProperties([
			"filter" => "drop-shadow(0 0 0 rgba(32, 33, 36, 0.28)) brightness(95%)",
			"-webkit-filter" => "drop-shadow(0 0 0 rgba(32, 33, 36, 0.28)) brightness(95%)"
		]);
		$color_rule = CssRule::rule()->withStyleProperties([
			"color" => "#0065d1",
			"filter" => "drop-shadow(0px -3px 6px rgba(32, 33, 36, 0.28)) brightness(1)",
			"-webkit-filter" => "drop-shadow(0px -3px 4px rgba(32, 33, 36, 0.28)) brightness(1)"
		]);
		$max_width_rule = CssRule::rule()->withStyleProperties([
			"max-width" => "100%",
			"opacity" => 1
		]);
		$choices = $this->getChoices();
		if($this->getAllFlag()){
			array_unshift($choices, $this->chooseAll());
		}
		$charcount = 0;
		foreach($choices as $choice){
			$keyword = $choice->getValue();
			$label_string = $choice->getLabelString();
			if(strlen($label_string) > $charcount){
				$charcount = strlen($label_string);
			}
			$tab_contents_selector = new ElementSelector();
			if(!$choice->getAllFlag()){
				$tab_contents_selector->attribute("tab", $keyword);
			}else{
				$tab_contents_selector->attribute("tab");
			}
			// display tabbed contents
			$inline_block_rule->pushSelector($this->getPositiveInputSelector($keyword)->sibling(ElementSelector::element("div")->descendant($tab_contents_selector)));
			// dim unselected tabs
			// XXX TODO this is using negative logic
			$filter_rule->pushSelector(
				$this->getNegativeInputSelector($keyword)->sibling(
					ElementSelector::elementClass("tab_labels")->child(
						$this->getPositiveLabelSelector($keyword)
					)
				)
			);
			// color selected font
			$color_rule->pushSelector(
				$this->getPositiveInputSelector($keyword)->sibling(
					ElementSelector::elementClass("tab_labels")->child(
						$this->getPositiveLabelSelector($keyword)
					)
				)
			);
			// tab highlight selector
			$max_width_rule->pushSelector(
				$this->getPositiveInputSelector($keyword)->sibling(
					ElementSelector::elementClass("tab_labels")->child(
						$this->getPositiveLabelSelector($keyword)->pseudoelement("after")
					)
				)
			);
		}
		$id = $this->getLabelContainerId();
		$charcount = $charcount / 2 + 1.5;
		$id_selector = ElementSelector::id($id);
		$id_selector->pushCoselector(ElementSelector::elementClass("tab_labels"));
		$width_rule = CssRule::rule()->withStyleProperties([
			"width" => "{$charcount}em"
		])->withSelectors(
			$id_selector->child(
				ElementSelector::element("label")->attribute("tab")
			)
		);
		$style->appendChild($inline_block_rule, $color_rule, $filter_rule, $max_width_rule, $width_rule);
		return $style;
	}

	protected function getNegativeLogicStyleElement(){
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function getDynamicStyleElement(){
		$f = __METHOD__;
		try{
			if(!$this->hasChoices()){
				Debug::error("{$f} mutex options are undefined");
			}
			$logic = $this->getSelectorLogic();
			if($logic === SIGNAGE_BIT_POSITIVE){
				$style = $this->getPositiveLogicStyleElement();
			}elseif($logic === SIGNAGE_BIT_NEGATIVE){
				$style = $this->getNegativeLogicStyleElement();
			}else{
				Debug::error("{$f} neither of the above");
			}
			return $style;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
