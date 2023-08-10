<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\common\ReusableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

abstract class CompoundElement extends Element
{

	protected $components;

	public abstract function generateComponents();

	public function hasComponents(): bool
	{
		return ! empty($this->components);
	}

	public function setComponents($components)
	{
		$f = __METHOD__; //CompoundElement::getShortClass()."(".static::getShortClass().")->setComponents()";
		if (! is_array($components) || empty($components)) {
			Debug::error("{$f} invalid components");
		}
		return $this->components = $components;
	}

	public final function getComponents(): ?array
	{
		$f = __METHOD__; //CompoundElement::getShortClass()."(".static::getShortClass().")->getComponents()";
		$print = false;
		if ($print) {
			if ($this->hasContext()) {
				$context = $this->getContext();
				$cc = $context->getClass();
				if ($context instanceof Datum) {
					$cn = $context->getColumnName();
					Debug::print("{$f} context is a {$cc} named \"{$cn}\"");
				} else {
					Debug::print("{$f} context is a {$cc}");
				}
			} else {
				Debug::print("{$f} context is undefined");
			}
		}
		if (! $this->hasComponents()) {
			if ($print) {
				Debug::print("{$f} generating components");
			}
			$components = $this->generateComponents();
			if (empty($components)) {
				if ($print) {
					Debug::warning("{$f} components returned null");
				}
				return null;
			}
			return $this->setComponents($components);
		} elseif ($print) {
			Debug::print("{$f} returning already generated components");
		}
		return $this->components;
	}

	public function echo(bool $destroy = false): void
	{
		$f = __METHOD__; //CompoundElement::getShortClass()."(".static::getShortClass().")->echo()";
		$print = false;
		$this->generateContents();
		// XXX copied from Element->echo
		if (! $this->getAllocatedFlag()) {
			Debug::warning("{$f} this object was already deleted");
			$this->debugPrintRootElement();
		} elseif ($this->hasWrapperElement()) {
			$wrapper = $this->getWrapperElement();
			if ($print) {
				Debug::print("{$f} this element has a wrapper -- echoing it now");
				if ($wrapper->hasStyleProperties()) {
					Debug::print("{$f} wrapper has the following inline style properties:");
					Debug::printArray($wrapper->getStyleProperties());
				} else {
					Debug::print("{$f} wrapper does not have style properties");
				}
			}
			$this->setWrapperElement(null);
			$wrapper->appendChild($this);
			$wrapper->echo($destroy);
			if (! $destroy) {
				$wrapper->removeChild($this);
				$this->setWrapperElement($wrapper);
			}
			return;
		}
		// $lang = getCurrentUserLanguagePreference();
		if ($this->getHTMLCacheableFlag() && $this->isCacheable() && HTML_CACHE_ENABLED) {
			if (cache()->hasFile($this->getCacheKey() . ".html")) {
				if ($print) {
					Debug::print("{$f} cached HTML is defined");
				}
				echo cache()->getFile($this->getCacheKey() . ".html");
				return;
			} else {
				if ($print) {
					Debug::print("{$f} HTML is not yet cached");
				}
				$cache = true;
				ob_start();
			}
		} else {
			if ($print) {
				Debug::print("{$f} this object is not cacheable");
			}
			$cache = false;
		}
		if ($this->hasPredecessors()) {
			$predecessors = $this->getPredecessors();
			foreach ($predecessors as $p) {
				$p->echo($destroy);
			}
		}
		$components = $this->getComponents();
		if (! empty($components)) {
			foreach ($components as $component) {
				$component->echo($destroy);
			}
		}
		if ($this->hasSuccessors()) {
			$successors = $this->getSuccessors();
			foreach ($successors as $s) {
				$s->echo($destroy);
			}
		}
		// XXX copied from Element->echo
		if ($cache) {
			if ($print) {
				Debug::print("{$f} about to update cache");
			}
			$html = ob_get_clean();
			cache()->setFile($this->getCacheKey() . ".html", $html, time() + 30 * 60);
			echo $html;
			unset($html);
		} elseif ($print) {
			Debug::print("{$f} nothing to cache");
		}
		if ($destroy && ! $this instanceof ReusableInterface) {
			// unset($this->successorNodes);
			$this->dispose();
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->components);
	}

	public function hasComponent($name)
	{
		return $this->hasComponents() && array_key_exists($name, $this->components);
	}

	public function setComponent($name, $component)
	{
		if (! isset($this->components) || ! is_array($this->components)) {
			$this->components = [];
		}
		return $this->components[$name] = $component;
	}

	public function getComponent($component_name)
	{
		$f = __METHOD__; //CompoundElement::getShortClass()."(".static::getShortClass().")->getComponent()";
		if (! $this->hasComponents()) {
			Debug::error("{$f} component \"{$component_name}\" is undefined");
		}
		return $this->components[$component_name];
	}

	public function echoJson(bool $destroy = false): void
	{
		$f = __METHOD__; //CompoundElement::getShortClass()."(".static::getShortClass().")->echoJson()";

		// XXX copied from Element->echoJson
		$print = false;
		if ($this->getTemplateFlag()) {
			Debug::print($this->__toString());
			Debug::error("{$f} should not be echoing a templated object");
		} elseif ($this->hasWrapperElement()) {
			$wrapper = $this->getWrapperElement();
			$this->setWrapperElement(null);
			$wrapper->appendChild($this);
			$wrapper->echoJson($destroy);
			if (! $destroy) {
				$wrapper->removeChild($this);
				$this->setWrapperElement($wrapper);
			} else {
				$this->dispose();
				// unset($this->parentNode);
				$this->setSuccessors(null);
			}
			return;
		} elseif (! $this->hasParentNode()) {
			$this->echoOrphan($destroy);
			return;
		}

		$this->generateContents();
		if ($this->hasPredecessors()) {
			$predecessors = $this->getPredecessors();
			foreach ($predecessors as $p) {
				$p->echoJson($destroy);
				echo ",";
			}
		}
		if (! $this->hasComponents()) {
			$this->generateComponents();
		}
		$components = $this->getComponents();
		if (! empty($components)) {
			$i = 0;
			foreach ($components as $component) {
				if ($i ++ > 0) {
					echo ",";
				}
				$component->echoJson($destroy);
			}
		} elseif ($print) {
			Debug::warning("{$f} components array is empty");
		}
		if ($this->hasSuccessors()) {
			$successors = $this->getSuccessors();
			$i = 0;
			foreach ($successors as $s) {
				if ($i ++ > 0) {
					echo ",";
				}
				$s->echoJson($destroy);
			}
		}
		if ($destroy) {
			// unset($this->successorNodes);
			$this->dispose();
		}
	}
}
