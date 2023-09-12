<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpanderElement;

class ExpandingEditForm extends ExpanderElement
{

	protected $formClass;

	protected $action;

	protected $successCallback;

	protected $errorCallback;

	public function setFormClass($form_class)
	{
		$f = __METHOD__;
		if(!is_string($form_class)) {
			Debug::error("{$f} received a parameter that is not a string");
		}elseif(! class_exists($form_class)) {
			Debug::error("{$f} form class \"{$form_class}\" is not a class");
		}
		return $this->formClass = $form_class;
	}

	public function hasFormClass()
	{
		return ! empty($this->formClass) && class_exists($this->formClass);
	}

	public function getFormClass()
	{
		$f = __METHOD__;
		if(!$this->hasFormClass()) {
			Debug::error("{$f} form class is undefined");
		}
		return $this->formClass;
	}

	public function hasActionAttribute()
	{
		return ! empty($this->action);
	}

	public function setActionAttribute($action)
	{
		return $this->action = $action;
	}

	public function getActionAttribute(): ?string
	{
		$f = __METHOD__;
		if(!$this->hasActionAttribute()) {
			Debug::error("{$f} action attribute is undefined");
		}
		return $this->action;
	}

	public function getErrorCallback()
	{
		$f = __METHOD__;
		if(!$this->hasErrorCallback()) {
			Debug::error("{$f} error callback is undefined");
		}
		return $this->errorCallback;
	}

	public function setErrorCallback($callback_error)
	{
		return $this->errorCallback = $callback_error;
	}

	public function hasErrorCallback()
	{
		return ! empty($this->errorCallback);
	}

	public function getSuccessCallback()
	{
		$f = __METHOD__;
		if(!$this->hasSuccessCallback()) {
			static::debugSuccess("{$f} success callback is undefined");
		}
		return $this->successCallback;
	}

	public function setSuccessCallback($callback_success)
	{
		return $this->successCallback = $callback_success;
	}

	public function hasSuccessCallback()
	{
		return ! empty($this->successCallback);
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setTriggerInputType(INPUT_TYPE_CHECKBOX);
		$this->setCollapseLabelStatus(false);
		$this->setExpandLabelString(_("Edit"));
		$this->setCollapseLabelString(_("Cancel"));
	}

	public function bindContext($context)
	{
		$short = $context->getDataType();
		$this->setExpandTriggerInputIdAttribute(GetColumnValueCommand::concatIndex("expand_update_{$short}-", $context, $context->getIdentifierName()));
		return parent::bindContext($context);
	}

	public function getElementTag(): string
	{
		if(!$this->hasElementTag()) {
			return $this->setElementTag("div");
		}
		return parent::getElementTag();
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__;
		$print = false;
		// $use_case = app()->getUseCase();
		$mode = $this->getAllocationMode();
		$context = $this->getContext();
		$form_class = $this->getFormClass();
		if($print) {
			Debug::print("{$f} about to create a new {$form_class}");
		}
		$form = new $form_class($mode);
		if($this->hasScope()) {
			$form->setScope($this->getResolvedScope());
		}
		$form->setIdOverride("edit_form");
		if($this->hasActionAttribute()) {
			$action = $this->getActionAttribute();
			$form->setActionAttribute($action);
		}
		if($this->hasErrorCallback()) {
			$callback_error = $this->getErrorCallback();
			$form->setErrorCallback($callback_error);
		}
		if($this->hasSuccessCallback()) {
			$callback_success = $this->getSuccessCallback();
			$form->setSuccessCallback($callback_success);
		}
		$form->setImportedCollapseLabel($this->createCollapseLabelElement());
		$form->bindContext($context);
		$this->setExpanderContents($form);
		return $this->getChildNodes();
	}
}
