<?php

namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\OptionsTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ActionAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\InputElement;
use JulianSeymour\PHPWebApplicationFramework\input\MultipleAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\input\RequiredAttributeTrait;
use Exception;

class SelectInput extends InputElement{

	use ActionAttributeTrait;
	use MultipleAttributeTrait;
	use MultipleChoiceInputTrait;
	use OptionsTrait;
	use RequiredAttributeTrait;

	public static function isEmptyElement(): bool{
		return false;
	}

	public function configure(?AjaxForm $form=null): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::configure($form);
		$datum = $this->getContext();
		if(!$this->hasLabelString() && $datum->hasHumanReadableName()){
			$this->setLabelString($datum->getHumanReadableName());
		}
		if(!$this->hasPredecessors() && $this->hasLabelString()){
			$span = new SpanElement($this->getAllocationMode());
			$span->setInnerHTML($this->getLabelString());
			$this->pushPredecessor($span);
		}
		if(!$datum->isNullable() && !$datum->hasDefaultValue()){
			$this->setRequiredAttribute("required");
		}
		if(!$this->hasWrapperElement()){
			$this->setWrapperElement(new DivElement());
		}
		if($this->hasOptions()){
			if($print){
				Debug::print("{$f} options are defined");
			}
			return $this->getOptions()->configure($form);
		}elseif($print){
			Debug::print("{$f} this object does not have options");
		}
		return $ret;
	}

	public function getOptions(): MultipleOptions{
		$f = __METHOD__;
		if($this->hasOptions()){
			return $this->options;
		}
		$context = $this->hasContext() ? $this->getContext() : null;
		$options = new MultipleOptions($this->getAllocationMode(), $context);
		return $this->setOptions($options);
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				if($this->hasContext()){
					$context = $this->getContext();
					if(is_object($context)){
						$cc = $context->getClass();
						if($context instanceof Datum){
							$cn = $context->getName();
							Debug::print("{$f} context is a {$cc} named \"{$cn}\"");
						}else{
							Debug::print("{$f} context is an object of class \"{$cc}\"");
						}
					}else{
						$gottype = gettype($context);
						Debug::print("{$f} context is a {$gottype}");
					}
				}else{
					Debug::print("{$f} context is undefined");
				}
			}
			if($this->hasChildNodes()){
				if($print){
					Debug::print("{$f} child nodes were already generated");
				}
				return $this->hasChildNodes() ? $this->getChildNodes() : [];
			}elseif($print){
				Debug::print("{$f} generating child nodes now");
			}
			$this->appendChild($this->getOptions());
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getNoSelectInputOptionsMessage(){
		return _("Unavailable");
	}

	public static function getElementTagStatic(): string{
		return INPUT_TYPE_SELECT;
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_SELECT;
	}

	public function hasInputs(): bool{
		return $this->hasOptions() && $this->getOptions()->hasInputs();
	}

	public function getInputs(): array{
		return $this->getOptions()->getInputs();
	}

	public function setInputs(?array $inputs): ?array{
		return $this->getOptions()->setInputs($inputs);
	}

	public function hasInput(string $name): bool{
		return $this->hasOptions() && $this->getOptions()->hasInput($name);
	}

	public function getInput($field){
		return $this->getOptions()->getInput($field);
	}

	public function setChoices(?array $choices):?array{
		return $this->getOptions()->setChoices($choices);
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->choiceGenerator, $deallocate);
		$this->release($this->options, $deallocate);
	}
}

