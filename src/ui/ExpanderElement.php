<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\avatar\ProfileImageThumbnailForm;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\Event;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use Exception;

class ExpanderElement extends Element{

	protected $collapseId;

	protected $collapseLabelString;

	protected $estimatedHeight;

	protected $expandId;

	protected $expandLabelString;

	protected $expandingContainer;

	protected $triggerInput;

	// = false;
	protected $triggerInputName;

	protected $triggerInputType;

	public static function declareFlags():?array{
		return array_merge(parent::declareFlags(), [
			"checked",
			"collapseLabelStatus",
			"contentsDefined",
		]);
	}
	
	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"checked",
			"collapseLabelStatus"
		]);
	}
	
	protected function afterConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int{
		$ret = parent::afterConstructorHook($mode, $context);
		$this->addClassAttribute("expander");
		$this->setCollapseLabelStatus(true);
		$this->setExpanderContentsDefined(false);
		return $ret;
	}

	public function releaseExpandingContainer(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->getAllocatedFlag()){
			Debug::error("{$f} allocated flag is not set for this ".$this->getDebugString());
		}elseif(!$this->hasExpandingContainer()){
			Debug::error("{$f} don't call this unless the expanding container is defined");
		}
		$ec = $this->getExpandingContainer();
		unset($this->expandingContainer);
		if($this->hasAnyEventListener("releaseExpandingCOntainer")){
			$this->dispatchEvent(new Event("releaseExpandingContainer", [
				"expandingConteiner" => $ec,
				"recursive" => false
			]));
		}
		$this->release($ec, $deallocate);
	}
	
	public function dispose(bool $deallocate=false): void{
		if($this->hasExpandingContainer()){
			$this->releaseExpandingContainer($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->collapseId, $deallocate);
		$this->release($this->collapseLabelString, $deallocate);
		$this->release($this->estimatedHeight, $deallocate);
		$this->release($this->expandId, $deallocate);
		$this->release($this->expandLabelString, $deallocate);
		if(
			$this->hasTriggerInput() 
			&& $this->triggerInput instanceof HitPointsInterface 
			&& $this->triggerInput->getAllocatedFlag()
		){
			$this->release($this->triggerInput, $deallocate);
		}else{
			unset($this->triggerInput);
		}
		$this->release($this->triggerInputName, $deallocate);
		$this->release($this->triggerInputType, $deallocate);
	}

	public function setTriggerInputNameAttribute($name){
		if($this->hasTriggerInputNameAttribute()){
			$this->release($this->triggerInputName);
		}
		return $this->triggerInputName = $this->claim($name);
	}

	public function hasTriggerInputType():bool{
		return isset($this->triggerInputType);
	}

	public function getTriggerInputType(){
		$f = __METHOD__;
		if(!$this->hasTriggerInputType()){
			Debug::error("{$f} trigger input type is undefined");
		}
		return $this->triggerInputType;
	}

	public function setTriggerInputChecked(bool $checked=true):bool{
		return $this->setFlag("checked", $checked);
	}

	public function setTriggerInputType($type){
		if($this->hasTriggerInputType()){
			$this->release($this->triggerInputType);
		}
		return $this->triggerInputType = $this->claim($type);
	}

	public function hasExpandLabelString():bool{
		return isset($this->expandLabelString);
	}

	public function getExpandLabelString(){
		if(!$this->hasExpandLabelString()){
			return _("Expand");
		}
		return $this->expandLabelString;
	}

	public function setExpandLabelString($s){
		if($this->hasExpandLabelString()){
			$this->release($this->expandLabelString);
		}
		return $this->expandLabelString = $this->claim($s);
	}

	public function hasCollapseLabelString():bool{
		return isset($this->collapseLabelString);
	}

	public function getCollapseLabelString(){
		if(!$this->hasCollapseLabelString()){
			return _("Collapse");
		}
		return $this->collapseLabelString;
	}

	public function setCollapseLabelString($s){
		if($this->hasCollapseLabelString()){
			$this->release($this->collapseLabelString);
		}
		return $this->collapseLabelString = $this->claim($s);
	}

	public function getTriggerInputClass():string{
		$f = __METHOD__;
		try{
			$type = $this->getTriggerInputType();
			switch($type){
				case INPUT_TYPE_CHECKBOX:
					return CheckboxInput::class;
				case INPUT_TYPE_RADIO:
					return RadioButtonInput::class;
				default:
					Debug::error("{$f} invalid form input type \"{$type}\"");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasExpandTriggerInputIdAttribute():bool{
		return isset($this->expandId);
	}

	public function getExpandTriggerInputIdAttribute(){
		$f = __METHOD__;
		if(!$this->hasExpandTriggerInputIdAttribute()){
			Debug::error("{$f} expand trigger input ID attribute is undefined");
		}
		return $this->expandId;
	}

	public function setExpandTriggerInputIdAttribute($id){
		if($this->hasExpandTriggerInputIdAttribute()){
			$this->release($this->expandId);
		}
		return $this->expandId = $this->claim($id);
	}

	public function hasCollapseTriggerInputIdAttribute():bool{
		return isset($this->collapseId);
	}

	public function getCollapseTriggerInputIdAttribute(){
		$f = __METHOD__;
		try{
			$type = $this->getTriggerInputType();
			switch($type){
				case INPUT_TYPE_CHECKBOX:
					return $this->getExpandTriggerInputIdAttribute();
				case INPUT_TYPE_RADIO:
					if(!$this->hasCollapseTriggerInputIdAttribute()){
						Debug::error("{$f} collapse trigger ID attribute is undefined");
					}
					return $this->collapseId;
				default:
					Debug::error("{$f} invalid form input type \"{$type}\"");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setCollapseTriggerInputIdAttribute($id){
		if($this->hasCollapseTriggerInputIdAttribute()){
			$this->release($this->collapseId);
		}
		return $this->collapseId = $this->claim($id);
	}

	public function setCollapseLabelStatus(bool $s=true):bool{
		return $this->setFlag('collapseLabelStatus', $s);
	}

	public function getCollapseLabelStatus():bool{
		return $this->getFlag("collapseLabelStatus");
	}

	public function hasTriggerInputNameAttribute():bool{
		return isset($this->triggerInputName);
	}

	public function getTriggerInputNameAttribute(){
		$f = __METHOD__;
		if(!$this->hasTriggerInputNameAttribute()){
			Debug::error("{$f} trigger input name attribute is undefined");
		}
		return $this->triggerInputName;
	}

	public function hasTriggerInput():bool{
		return isset($this->triggerInput);
	}

	public function setExpanderContentsDefined(bool $defined=true):bool{
		return $this->setFlag("contentsDefined", $defined);
	}

	public function getExpanderContentsDefined():bool{
		return $this->getFlag("contentsDefined");
	}

	public function expand():ExpanderElement{
		$this->getTriggerInput()->check();
		return $this;
	}

	public function collapse():ExpanderElement{
		$input = $this->getTriggerInput();
		if($input->hasCheckedAttribute()){
			$input->removeCheckedAttribute();
		}
		return $this;
	}

	public function getTriggerInput(){
		$f = __METHOD__;
		$print = false;
		if($this->hasTriggerInput()){
			return $this->triggerInput;
		}
		$mode = $this->getAllocationMode();
		$tic = $this->getTriggerInputClass();
		$input = new $tic($mode);
		if($print){
			if($this->hasNestedFormClass()){
				Debug::print("{$f} nested form class is \"{$this->nestedFormClass}\"");
			}else{
				Debug::print("{$f} nested form class is undefined");
			}
			$this->announce($input);
		}
		$input->addClassAttribute("hidden");
		if($this->getTriggerInputType() === INPUT_TYPE_RADIO){
			$input->setNameAttribute($this->getTriggerInputNameAttribute());
		}
		$input->setIdAttribute($this->getExpandTriggerInputIdAttribute());
		$input->setNoUpdateFlag(true);
		return $this->setTriggerInput($input);
	}

	public function setTriggerInput($input){
		if($this->hasTriggerInput()){
			$this->release($this->triggerInput);
		}
		return $this->triggerInput = $this->claim($input);
	}
	
	protected function getSelfGeneratedPredecessors(): ?array{
		return [
			$this->getTriggerInput()
		];
	}

	protected function createLabelContainer(){
		$mode = $this->getAllocationMode();
		$label_container = new DivElement($mode);
		$label_container->addClassAttribute("label_container");
		$open_label = $this->createExpandLabelElement();
		$label_container->appendChild($open_label);
		if($this->getCollapseLabelStatus()){
			$close_label = $this->createCollapseLabelElement();
			$label_container->appendChild($close_label);
		}
		return $label_container;
	}

	protected function createExpandLabelElement(){
		$mode = $this->getAllocationMode();
		$open_label = new LabelElement($mode);
		$open_label->addClassAttribute("expand_label");
		$open_label->setInnerHTML($this->getExpandLabelString());
		$for = $this->getExpandTriggerInputIdAttribute();
		$open_label->setForAttribute($for);
		$id = new ConcatenateCommand("expand_label-", $for);
		$open_label->setIdAttribute($id);
		if(!$this->getTemplateFlag()){
			deallocate($id);
		}
		return $open_label;
	}

	public function createCollapseLabelElement(){
		$f = __METHOD__;
		$print = false;
		$mode = $this->getAllocationMode();
		if($print){
			Debug::print("{$f} child generation mode is \"{$mode}\"");
		}
		$close_label = new LabelElement($mode);
		$close_label->addClassAttribute("collapse_label");
		$close_label->setInnerHTML($this->getCollapseLabelString());
		$for = $this->getCollapseTriggerInputIdAttribute();
		//if($this->getTriggerInputType() === INPUT_TYPE_CHECKBOX){
			//if(is_object($for)){
				/*if($print){
					$idc = $for->getClass();
					Debug::print("{$f} this is a checkbox-based expander -- about to replicate command of class \"{$idc}\"");
				}*/
				//$id = new ConcatenateCommand("collapse_label-", $for);
			//}else{
				//$id = new ConcatenateCommand("collapse_label-", $for);
			//}
		//}else{
			$id = new ConcatenateCommand("collapse_label-", $for);
		//}
		$close_label->setForAttribute($for);
		$close_label->setIdAttribute($id);
		if(!$this->getTemplateFlag()){
			deallocate($id);
		}
		return $close_label;
	}

	public function hasEstimatedHeight(): bool{
		return isset($this->estimatedHeight);
	}

	public function getEstimatedHeight(): string{
		$f = __METHOD__;
		if(!$this->hasEstimatedHeight()){
			Debug::error("{$f} estimated height is undefined");
		}
		return $this->estimatedHeight;
	}

	public function setEstimatedHeight(?string $height): ?string{
		if($this->hasEstimatedHeight()){
			$this->release($this->estimatedHeight);
		}
		return $this->estimatedHeight = $this->claim($height);
	}

	public function getExpandingContainer(){
		if($this->hasExpandingContainer()){
			return $this->expandingContainer;
		}
		return $this->setExpandingContainer($this->createExpandingContainer());
	}
	
	protected function createExpandingContainer(){
		$f = __METHOD__;
		$mode = $this->getAllocationMode();
		$expandme = new DivElement($mode);
		$expandme->addClassAttribute("expand_me");
		if($this->hasEstimatedHeight()){
			$expandme->setStyleProperty("max-height", $this->getEstimatedHeight());
			$expandme->setStyleProperty("overflow", "auto");
		}
		return $expandme;
	}

	public function setExpandingContainer($expandme){
		if($this->hasExpandingContainer()){
			$this->releaseExpandingContainer();
		}
		if($expandme instanceof HitPointsInterface){
			$that = $this;
			$random = sha1(random_bytes(32));
			$closure1 = function(DeallocateEvent $event, HitPointsInterface $target)
			use ($that, $random){
				$target->removeEventListener($event);
				if($that->hasEventListener("releaseExpandingContainer", $random)){
					$that->removeEventListener("releaseExpandingContainer", $random);
				}
				if($that->hasExpandingContainer()){
					$that->releaseExpandingContainer();
				}
			};
			$expandme->addEventListener(EVENT_DEALLOCATE, $closure1, $random);
			$closure2 = function(Event $event, ExpanderElement $target)
			use ($random){
				$target->removeEventListener($event);
				$expandme = $event->getProperty("expandngContainer");
				if($expandme->hasEventListener(EVENT_DEALLOCATE, $random)){
					$expandme->removeEventListener(EVENT_DEALLOCATE, $random);
				}
			};
			$this->addEventListener("releaseExpandingContainer", $closure2, $random);
			return $this->expandingContainer = $this->claim($expandme);
		}
		return $this->expandingContainer = $expandme;
	}
	
	public function hasExpandingContainer():bool{
		return isset($this->expandingContainer);
	}

	public function setExpanderContents($contents){
		$f = __METHOD__;
		try{
			$label_container = $this->createLabelContainer();
			$this->appendChild($label_container);
			if($this->hasExpandingContainer()){
				$expandme = $this->getExpandingContainer();
			}else{
				$expandme = $this->createExpandingContainer();
			}
			if(is_array($contents)){
				foreach($contents as $c){
					if($c instanceof Command){
						$expandme->resolveTemplateCommand($c);
					}else{
						$expandme->appendChild($c);
					}
				}
			}elseif($contents instanceof Command){
				$expandme->resolveTemplateCommand($contents);
			}else{
				$expandme->appendChild($contents);
			}
			$this->appendChild($expandme);
			$this->setExpanderContentsDefined(true);
			return $contents;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function setTriggerInputIdAttribute($id){
		return $this->setExpandTriggerInputIdAttribute($this->setCollapseTriggerInputIdAttribute($id));
	}
}
