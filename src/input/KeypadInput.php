<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\AutocompleteAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use Exception;

abstract class KeypadInput extends InputElement{

	use AutocompleteAttributeTrait;

	protected $placeholderMode;

	public function configure(?AjaxForm $form=null): int{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($print){
			$did = $this->getDebugId();
			$decl = $this->getDeclarationLine();
		}
		if($this->getFlag("configured")){
			if($print){
				Debug::print("{$f} configured flag is already set. Debug ID is {$did}. Instantiated {$decl}");
			}
			return SUCCESS;
		}else{
			if($print){
				Debug::print("{$f} entered. Debug ID is {$did}. Instantiated {$decl}");
			}
			$this->setFlag("configured", true);
		}
		$ret = parent::configure($form);
		if($this->hasContext()){
			$datum = $this->getContext();
			$cn = $datum->getName();
		}else{
			$cn = "[undefined]";
		}
		if(!$this->hasLabelString() && $this->hasContext()){
			if($print){
				Debug::print("{$f} label string is undefined, and context is defined");
			}
			$context = $this->getContext();
			if($context instanceof Datum && $context->hasHumanReadableName()){
				if($print){
					Debug::print("{$f} setting label string from context's human readable name");
				}
				$hrvn = $context->getHumanReadableName();
				$this->setLabelString(substitute(_("Enter %1%"), $hrvn));
			}
		}elseif($print){
			if($this->hasContext()){
				$ls = $this->getLabelString();
				if(is_string($ls)){
					Debug::print("{$f} label string is already set to \"{$ls}\"");
				}else{
					Debug::print("{$f} label string is set to something other than a string");
				}
			}else{
				Debug::print("{$f} context is undefined");
			}
		}
		if($this->hasLabelString()){
			if($print){
				Debug::print("{$f} label string is defined");
			}
			$hrvn = $this->getLabelString();
			if(
				!$this instanceof TextareaInput && (
					(
						$this->hasPlaceholderMode() && 
						$this->getPlaceholderMode() === INPUT_PLACEHOLDER_MODE_SHRINK
					) || config()->getDefaultPlaceholderMode() === INPUT_PLACEHOLDER_MODE_SHRINK
				)
			){
				if($print){
					Debug::print("{$f} placeholder mode is \"shrink\"");
				}
				if(!$this instanceof RangeInput){
					if($print){
						Debug::print("{$f} this is not a range input");
					}
					$this->setPlaceholderAttribute("");
					if(!$this->hasWrapperElement()){
						if($print){
							Debug::print("{$f} input does not already have a wrapper");
						}
						$div1 = new DivElement();
						$div1->setStyleProperties([
							"position" => "relative",
							"display" => "inline-block",
							"width" => "100%"
						]);
						$div2 = new DivElement();
						$div2->setStyleProperties([
							"position" => "relative",
							"display" => ($this instanceof TextareaInput ? "inline-block" : "block")
						]);
						$div1->setWrapperElement($div2);
						$this->setWrapperElement($div1);
					}elseif($print){
						Debug::print("{$f} input already has a wrapper");
					}
					$span = new SpanElement($this->getAllocationMode());
					$span->addClassAttribute("placeholder_label");
					$span->setInnerHTML($hrvn);
					$this->unshiftSuccessor($span);
				}elseif($print){
					Debug::print("{$f} this is a range input");
				}
			}elseif(!$this->hasPlaceholderMode() || $this->hasPlaceholderMode() && $this->getPlaceholderMode() === INPUT_PLACEHOLDER_MODE_NORMAL || config()->getDefaultPlaceholderMode() === INPUT_PLACEHOLDER_MODE_NORMAL){
				if($print){
					Debug::print("{$f} normal placeholders");
				}
				if(!$this->hasPlaceholderAttribute()){
					$this->setPlaceholderAttribute($hrvn);
				}elseif($print){
					Debug::print("{$f} placeholder attribute is already defined");
				}
				if(!$this->hasWrapperElement()){
					$div = new DivElement();
					$div->setStyleProperties([
						"position" => "relative",
						"display" => "block",
						"width" => "100%"
					]);
					$this->setWrapperElement($div);
				}
			}elseif($print){
				Debug::print("{$f} none of the above");
			}
		}elseif($print){
			Debug::print("{$f} input for column \"{$cn}\" does not have a label string");
		}
		return $ret;
	}

	public function setPlaceholderMode($mode){
		if($this->hasPlaceholderMode()){
			$this->release($this->placeholderMode);
		}
		return $this->placeholderMode = $this->claim($mode);
	}

	public function hasPlaceholderMode(): bool{
		return isset($this->placeholderMode);
	}

	public function getPlaceholderMode(){
		return $this->placeholderMode;
	}

	public function setPlaceholderAttribute($txt){
		$f = __METHOD__;
		try{
			$print = false;
			if($txt instanceof ValueReturningCommandInterface){
				while($txt instanceof ValueReturningCommandInterface){
					$txt = $txt->evaluate();
				}
			}
			$formatted = str_replace("\"", "\\\"", $txt);
			$this->setAttribute("placeholder", $formatted);
			if(empty($formatted)){
				return $this->getPlaceholderAttribute();
			}elseif($print){
				Debug::print("{$f} returning \"{$formatted}\"");
			}
			return $this->getPlaceholderAttribute();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasPlaceholderAttribute():bool{
		$f = __METHOD__;
		$print = false;
		if($this->hasAttribute("placeholder")){
			if($print){
				Debug::print("{$f} yes, this input has a placeholder attribute");
			}
			if(!empty($this->getAttribute("placeholder"))){
				if($print){
					Debug::print("{$f} this input's placeholder attribute is not empty");
				}
				return true;
			}elseif($print){
				Debug::print("{$f} unfortunately, this input's placeholder attribute is empty");
			}
		}elseif($print){
			Debug::print("{$f} this input does not have a placeholder attribute");
		}
		return false;
	}

	public function getPlaceholderAttribute(){
		return $this->getAttribute("placeholder");
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}

	public function setDirectionNameAttribute($value){
		return $this->setAttribute("dirname", $value);
	}

	public function hasDirectionNameAttribute():bool{
		return $this->hasAttribute("dirname");
	}

	public function getDirectionNameAttribute(){
		return $this->getAttribute("dirname");
	}

	public function directionName($value){
		$this->setAttribute("dirname", $value);
		return $this;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->placeholderMode, $deallocate);
	}
}
