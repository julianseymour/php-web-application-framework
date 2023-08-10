<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class GenerateStaticFormButtonsCommand extends GenerateFormButtonsCommand
{

	/*
	 * protected $buttonContainer;
	 *
	 * public function __construct($form, $btn_container){
	 * parent::__construct($form);
	 * $this->setButtonContainer($btn_container);
	 * }
	 *
	 * public function setButtonContainer($bc){
	 * return $this->buttonContainer = $bc;
	 * }
	 *
	 * public function hasButtonContainer(){
	 * return isset($this->buttonContainer);
	 * }
	 *
	 * public function getButtonContainer(){
	 * $f = __METHOD__; //GenerateFormButtonsCommand::getShortClass()."(".static::getShortClass().")->getButtonContainer()";
	 * if(!$this->hasButtonContainer()){
	 * Debug::error("{$f} button container is undefined");
	 * }
	 * return $this->buttonContainer;
	 * }
	 */
	public static function extractAnyway()
	{
		return true;
	}

	public static function getCommandId(): string
	{
		return "generateStaticFormButtons";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //GenerateStaticFormButtonsCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		ErrorMessage::unimplemented($f);
	}
}
