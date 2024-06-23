<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpanderElement;
use function JulianSeymour\PHPWebApplicationFramework\release;

class ExpandingEditForm extends ExpanderElement{

	use FormClassTrait;
	use SuccessAndErrorCallbacksTrait;
	
	protected $action;

	public function hasActionAttribute():bool{
		return !empty($this->action);
	}

	public function setActionAttribute($action){
		return $this->action = $action;
	}

	public function getActionAttribute(): ?string{
		$f = __METHOD__;
		if(!$this->hasActionAttribute()){
			Debug::error("{$f} action attribute is undefined");
		}
		return $this->action;
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setTriggerInputType(INPUT_TYPE_CHECKBOX);
		$this->setCollapseLabelStatus(false);
		$this->setExpandLabelString(_("Edit"));
		$this->setCollapseLabelString(_("Cancel"));
	}

	public function bindContext($context){
		$short = $context->getDataType();
		$this->setExpandTriggerInputIdAttribute(GetColumnValueCommand::concatIndex("expand_update_{$short}-", $context, $context->getIdentifierName()));
		return parent::bindContext($context);
	}

	public function getElementTag(): string{
		if(!$this->hasElementTag()){
			return $this->setElementTag("div");
		}
		return parent::getElementTag();
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		$print = false;
		// $use_case = app()->getUseCase();
		$mode = $this->getAllocationMode();
		$context = $this->getContext();
		$form_class = $this->getFormClass();
		if($print){
			Debug::print("{$f} about to create a new {$form_class}");
		}
		$form = new $form_class($mode);
		if($this->hasScope()){
			$form->setScope($this->getResolvedScope());
		}
		$form->setIdOverride("edit_form");
		if($this->hasActionAttribute()){
			$action = $this->getActionAttribute();
			$form->setActionAttribute($action);
		}
		if($this->hasErrorCallback()){
			$callback_error = $this->getErrorCallback();
			$form->setErrorCallback($callback_error);
		}
		if($this->hasSuccessCallback()){
			$callback_success = $this->getSuccessCallback();
			$form->setSuccessCallback($callback_success);
		}
		$form->setImportedCollapseLabel($this->createCollapseLabelElement());
		$form->bindContext($context);
		$this->setExpanderContents($form);
		return $this->hasChildNodes() ? $this->getChildNodes() : [];
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->action, $deallocate);
		$this->release($this->errorCallback, $deallocate);
		$this->release($this->formClass, $deallocate);
		$this->release($this->successCallback, $deallocate);
	}
}
