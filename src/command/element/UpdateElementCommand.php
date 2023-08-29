<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use Exception;

class UpdateElementCommand extends MultipleElementCommand{

	use EffectTrait;

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"innerH"
		]);
	}

	public function getInnerFlag(): bool{
		return $this->getFlag("inner");
	}

	public function setInnerFlag(bool $value = true): bool{
		return $this->setFlag("inner", $value);
	}

	public function inner(bool $value = true): UpdateElementCommand{
		$this->setInnerFlag($value);
		return $this;
	}

	public function __construct(...$elements){
		$f = __METHOD__;
		try {
			$print = false;
			$final_elements = [];
			if (count($elements) === 1 && is_array($elements[0])) {
				if ($print) {
					Debug::print("{$f} did not unpack array");
				}
				$elements = $elements[0];
			} else {
				if ($print) {
					Debug::print("{$f} array is unpacked");
				}
				$temp = [];
				foreach ($elements as $e) {
					$temp[$e->getIdAttribute()] = $e;
				}
				$elements = &$temp;
			}
			foreach ($elements as $old_id => $element) {
				if (is_array($element)) {
					Debug::error("{$f} element is an array");
				} elseif (is_string($element)) {
					Debug::error("{$f} this function no longer accepts strings");
				} elseif (! $element->getContentsGeneratedFlag()) {
					if ($print) {
						Debug::print("{$f} element has not been finalized");
					}
					$element->generateContents();
				} elseif ($print) {
					Debug::print("{$f} element has already been finalized");
				}
				if ($element->hasPredecessors()) {
					$predecessors = $element->getPredecessors();
					$element->setPredecessors(null);
					foreach ($predecessors as $p) {
						if ($p instanceof Element && $p->getNoUpdateFlag()) {
							if ($print) {
								Debug::print("{$f} skipping predecessor element with noUpdate flag");
								continue;
							}
						}
						$final_elements[$p->getIdAttribute()] = $p;
					}
				}
				$element->setReplacementId($old_id);
				$final_elements[$old_id] = $element; // array_push($final_elements, $element);
				if ($element->hasSuccessors()) {
					$successors = $element->getSuccessors();
					$element->setSuccessors(null);
					foreach ($successors as $s) {
						if ($s instanceof Element && $s->getNoUpdateFlag()) {
							if ($print) {
								Debug::print("{$f} skipping successor element with noUpdate flag");
								continue;
							}
						}
						$final_elements[$s->getIdAttribute()] = $s; // array_push($final_elements, $s);
					}
				}
			}
			// $temp_elements = null;
			foreach ($final_elements as $element) {
				$element->setCatchReportedSubcommandsFlag(true);
				$element->setSubcommandCollector($this);
				if (! $element->hasIdAttribute()) {
					// $element->setAttribute("temp_id", $element->removeAttribute("id"));
					$ec = is_object($element) ? $element->getClass() : gettype($element);
					Debug::warning("{$f} element of class \"{$ec}\" has no ID attribute");
					$element->announceYourself();
				}
			}
			if ($print) {
				$keys = array_keys($final_elements);
				Debug::print("{$f} about to call parent constructor with the following keys:");
				Debug::printArray($keys);
			}
			parent::__construct($final_elements);
			// $this->getElements();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		try {
			$i = 0;
			echo "\"elements\":";
			$elements = $this->getElements();
			$assoc = is_associative($elements);
			if ($assoc) {
				echo "{";
			} else {
				echo "[";
			}
			foreach ($elements as $key => $element) {
				if ($i ++ > 0) {
					echo ",";
				}
				if ($assoc) {
					echo "\"{$key}\":";
				}
				if ($element instanceof Element) {
					if ($element->getAllocationMode() === ALLOCATION_MODE_ULTRA_LAZY) {
						$element->appendSavedChildren($destroy);
					}
					if ($this->getInnerFlag()) {
						$element->setElementTag("fragment");
						$element->clearAttributes();
					}
				}
				Json::echo($element, $destroy, false);
			}
			if ($assoc) {
				echo "}";
			} else {
				echo "]";
			}
			echo ",";
			// Json::echoKeyValuePair("elements", $this->getElements(), $destroy);
			if ($this->hasEffect()) {
				Json::echoKeyValuePair("effect", $this->getEffect());
			}
			if ($this->getInnerFlag()) {
				Json::echoKeyValuePair("inner", 1);
			}
			parent::echoInnerJson($destroy);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getCommandId(): string{
		return 'update';
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->effect);
	}
}
