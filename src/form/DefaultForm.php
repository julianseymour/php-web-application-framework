<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\input\KeypadInput;

class DefaultForm extends AjaxForm{

	public function getReorderedColumnIndices(?DataStructure $ds):?array{
		if($ds == null){
			$ds = $this->getContext();
		}
		return $ds->getReorderedColumnIndices();
	}
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setStyleProperty("border", "1px solid");
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "default";
	}

	public static function getMethodAttributeStatic(): ?string
	{
		return HTTP_REQUEST_METHOD_POST;
	}

	public static function getActionAttributeStatic(): ?string{
		return app()->getUseCase()->getActionAttribute();
	}

	public static function getNewFormOption(): bool{
		return true;
	}

	public function bindContext($context){
		$f = __METHOD__;
		if(!$this->hasActionAttribute()) {
			Debug::error("{$f} don't bind context until you have an action attribute");
		}
		return parent::bindContext($context);
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		try{
			if($this->hasDirectives()) {
				if(false === array_search($name, $this->directives)) {
					Debug::error("{$f} illegal name attribute \"{$name}\"");
				}
			}
			switch ($name) {
				case DIRECTIVE_INSERT:
				case DIRECTIVE_UPDATE:
				case DIRECTIVE_DELETE:
					$button = $this->generateGenericButton($name);
					return [
						$button
					];
				default:
					Debug::error("{$f} invalid name attribute \"{$name}\"");
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getSubordinateFormClass($index){
		return static::class;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$div = new DivElement($mode);
			$div->setStyleProperties([
				"position" => "relative"
			]);
			$input->setWrapperElement($div);
			return parent::reconfigureInput($input);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array{
		$f = "DefaultForm(".static::getShortClass().")->getFormDataIndices()";
		try{
			$print = false;
			if(!$this->hasContext()) {
				Debug::error("{$f} context is undefined");
			}elseif($print){
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} entered, declared {$decl}");
			}
			$context = $this->getContext();
			$class = $context->getClass();
			$indices = [];
			//$columns = $context->getColumns();
			$reordered = $this->getReorderedColumnIndices($context);
			if($reordered === null){
				$reordered = $context->getColumnNames();
			}
			foreach($reordered as $index) {
				$datum = $context->getColumn($index);
				if($datum instanceof VirtualDatum) {
					if($print) {
						Debug::print("{$f} datum at index \"{$index}\" is virtual");
					}
					continue;
				}elseif($datum->getSensitiveFlag()) {
					if($print) {
						Debug::print("{$f} datum at index \"{$index}\" is sensitive");
					}
					continue;
				}elseif($datum->getNeverLeaveServer()) {
					if($print) {
						Debug::print("{$f} datum must never leave server");
					}
					continue;
				}elseif(!$datum->getAdminInterfaceFlag()) {
					if($print) {
						Debug::print("{$f} admin interface flag is undefined for datum at index \"{$index}\"");
					}
					continue;
				}elseif(!$datum->hasElementClass() && ! $datum instanceof StaticElementClassInterface) {
					if($print) {
						Debug::print("{$f} datum at index \"{$index}\" has no default input element class");
					}
					continue;
				}
				$indices[$index] = $datum->getElementClass();
				if($print) {
					Debug::print("{$f} set index \"{$index}\" to class \"{$indices[$index]}\"");
				}
			}
			if(empty($indices)) {
				Debug::error("{$f} indices array is empty for class \"{$class}\"");
			}elseif($print){
				Debug::printArray($indices);
			}
			return $indices;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		if($this->hasDirectives()) {
			return $this->directives;
		}
		$context = $this->getContext();
		return $context->isUninitialized() ? [
			DIRECTIVE_INSERT
		] : [
			DIRECTIVE_UPDATE,
			DIRECTIVE_DELETE
		];
	}
}
