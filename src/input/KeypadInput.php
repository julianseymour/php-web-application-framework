<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\AutocompleteAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use Exception;

abstract class KeypadInput extends InputElement
{

	use AutocompleteAttributeTrait;

	protected $placeholderMode;

	protected $placeholderLabel;

	public function configure(AjaxForm $form): int{
		$f = __METHOD__;
		$ret = parent::configure($form);
		$print = false;
		if ($this->hasContext()) {
			$datum = $this->getContext();
			$cn = $datum->getColumnName();
		} else {
			$cn = "[undefined]";
		}
		if ($this->hasLabelString()) {
			if ($print) {
				Debug::print("{$f} label string is defined");
			}
			$hrvn = $this->getLabelString(); // datum->getHumanReadableName();
			if ($this->getPlaceholderMode() === INPUT_PLACEHOLDER_MODE_SHRINK) {
				if ($print) {
					Debug::print("{$f} placeholder mode is \"shrink\"");
				}
				if (! $this instanceof RangeInput) {
					if ($print) {
						Debug::print("{$f} this is not a range input");
					}
					$this->setPlaceholderAttribute("");
					if (! $this->hasWrapperElement()) {
						if ($print) {
							Debug::print("{$f} input does not already have a wrapper");
						}
						$div = new DivElement();
						$div->addClassAttribute("thumbsize", "relative", "block");
						$this->setWrapperElement($div);
					} elseif ($print) {
						Debug::print("{$f} input already has a wrapper");
					}
					//if (! $this->hasSuccessors()) {
						if ($print) {
							Debug::print("{$f} input does not already have successors");
						}
						$span = new SpanElement($this->getAllocationMode());
						$span->addClassAttribute("placeholder_label");
						// $span->setForAttribute($this->getIdAttribute());
						$span->setInnerHTML($hrvn);
						$this->unshiftSuccessors($span);
					/*} elseif ($print) {
						Debug::print("{$f} input already has successors");
					}*/
				} elseif ($print) {
					Debug::print("{$f} this is a range input");
				}
			} else {
				if ($print) {
					Debug::print("{$f} placeholder mode is something besides shrink");
				}
				if (! $this->hasPlaceholderAttribute()) {
					$this->setPlaceholderAttribute(substitute(_("Enter %1%"), $hrvn));
				} elseif ($print) {
					Debug::print("{$f} placeholder attribute is already defined");
				}
			}
			// $this->setPlaceholderAttribute("");
		} elseif ($print) {
			Debug::print("{$f} input for column \"{$cn}\" does not have a label string");
		}
		return $ret;
	}

	public function setPlaceholderMode($mode){
		$f = __METHOD__;
		$print = false;
		if ($mode === null) {
			unset($this->placeholderMode);
			return null;
		}
		return $this->placeholderMode = $mode;
	}

	public function hasPlaceholderMode(): bool{
		return isset($this->placeholderMode);
	}

	public function getPlaceholderMode(){
		return $this->placeholderMode;
	}

	public function setPlaceholderAttribute($txt){
		$f = __METHOD__;
		try {
			$print = false;
			if ($txt instanceof ValueReturningCommandInterface) {
				while ($txt instanceof ValueReturningCommandInterface) {
					$txt = $txt->evaluate();
				}
			}
			$formatted = str_replace("\"", "\\\"", $txt);
			$this->setAttribute("placeholder", $formatted);
			if (empty($formatted)) {
				return $this->getPlaceholderAttribute();
			}elseif ($print) {
				Debug::print("{$f} returning \"{$formatted}\"");
			}
			return $this->getPlaceholderAttribute();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasPlaceholderLabel()
	{
		return isset($this->placeholderLabel);
	}

	protected function generateSuccessors(): ?array{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} entered; about to call parent function");
		}
		$nodes = $this->ejectSuccessors(); // parent::generateSuccessors();
		if (! $this->hasPlaceholderLabel()) {
			if ($print) {
				Debug::print("{$f} placeholder label is undefined");
			}
			return $nodes;
		} elseif ($print) {
			Debug::print("{$f} placeholder label is defined -- about to unshift it");
		}
		if (empty($nodes)) {
			if ($print) {
				Debug::print("{$f} there are no other successors");
			}
			$nodes = [
				$this->placeholderLabel
			];
		} else {
			if ($print) {
				Debug::print("{$f} unshifting placeholder label before existing successors");
			}
			array_unshift($nodes, $this->placeholderLabel);
		}
		if ($print) {
			$count = count($nodes);
			Debug::print("{$f} returning {$count} nodes");
			foreach ($nodes as $n) {
				Debug::print("{$f} {$n}");
			}
		}
		return $nodes;
	}

	public function hasPlaceholderAttribute(){
		$f = __METHOD__;
		$print = false;
		if ($this->hasAttribute("placeholder")) {
			if ($print) {
				Debug::print("{$f} yes, this input has a placeholder attribute");
			}
			if (! empty($this->getAttribute("placeholder"))) {
				if ($print) {
					Debug::print("{$f} this input's placeholder attribute is not empty");
				}
				return true;
			} elseif ($print) {
				Debug::print("{$f} unfortunately, this input's placeholder attribute is empty");
			}
		} elseif ($print) {
			Debug::print("{$f} this input does not have a placeholder attribute");
		}
		return false;
	}

	public function getPlaceholderAttribute()
	{
		return $this->getAttribute("placeholder");
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}

	public function setDirectionNameAttribute($value)
	{
		return $this->setAttribute("dirname", $value);
	}

	public function hasDirectionNameAttribute()
	{
		return $this->hasAttribute("dirname");
	}

	public function getDirectionNameAttribute()
	{
		return $this->getAttribute("dirname");
	}

	public function directionName($value)
	{
		$this->setAttribute("dirname", $value);
		return $this;
	}

	public function dispose(): void
	{
		parent::dispose();
		// unset($this->placeholderLabel);
		unset($this->placeholderMode);
	}
}
