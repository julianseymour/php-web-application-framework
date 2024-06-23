<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\input\choice\MultipleCheckboxes;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

abstract class CompoundElement extends Element{

	protected $components;

	public abstract function generateComponents();

	public function hasComponents(): bool{
		return !empty($this->components);
	}

	public function setComponents($components){
		$f = __METHOD__;
		if(!is_array($components) || empty($components)){
			Debug::error("{$f} invalid components");
		}elseif($this->hasComponents()){
			$this->release($this->components);
		}
		return $this->components = $this->claim($components);
	}

	public final function getComponents(): ?array{
		$f = __METHOD__;
		$print = false;
		if($print){
			if($this->hasContext()){
				$context = $this->getContext();
				$cc = $context->getClass();
				if($context instanceof Datum){
					$cn = $context->getName();
					Debug::print("{$f} context is a {$cc} named \"{$cn}\"");
				}else{
					Debug::print("{$f} context is a {$cc}");
				}
			}else{
				Debug::print("{$f} context is undefined");
			}
		}
		if(!$this->hasComponents()){
			if($print){
				Debug::print("{$f} generating components");
			}
			$components = $this->generateComponents();
			if(empty($components)){
				if($print){
					Debug::warning("{$f} components returned null");
				}
				return null;
			}
			return $this->setComponents($components);
		}elseif($print){
			Debug::print("{$f} returning already generated components");
		}
		return $this->components;
	}

	public function echo(bool $destroy = false): void{
		$f = __METHOD__;
		$print = false;
		$this->generateContents();
		//copied from Element->echo
		if(!$this->getAllocatedFlag()){
			Debug::warning("{$f} this object was already deleted");
			$this->debugPrintRootElement();
		}elseif($this->hasWrapperElement()){
			$wrapper = $this->getWrapperElement();
			if($print){
				Debug::print("{$f} this element has a wrapper -- echoing it now");
				if($wrapper->hasStyleProperties()){
					Debug::print("{$f} wrapper has the following inline style properties:");
					Debug::printArray($wrapper->getStyleProperties());
				}else{
					Debug::print("{$f} wrapper does not have style properties");
				}
			}
			$this->setWrapperElement(null);
			$wrapper->appendChild($this);
			$wrapper->echo($destroy);
			if(!$destroy){
				$wrapper->removeChild($this);
				$this->setWrapperElement($wrapper);
			}
			return;
		}
		if($this->getHTMLCacheableFlag() && $this->isCacheable() && HTML_CACHE_ENABLED){
			if(cache()->hasFile($this->getCacheKey() . ".html")){
				if($print){
					Debug::print("{$f} cached HTML is defined");
				}
				echo cache()->getFile($this->getCacheKey() . ".html");
				return;
			}else{
				if($print){
					Debug::print("{$f} HTML is not yet cached");
				}
				$cache = true;
				ob_start();
			}
		}else{
			if($print){
				Debug::print("{$f} this object is not cacheable");
			}
			$cache = false;
		}
		if($this->hasPredecessors()){
			$predecessors = $this->getPredecessors();
			foreach($predecessors as $p){
				$p->echo($destroy);
			}
		}
		$components = $this->getComponents();
		if(!empty($components)){
			foreach($components as $component){
				$component->echo($destroy);
			}
		}
		if($this->hasSuccessors()){
			$successors = $this->getSuccessors();
			foreach($successors as $s){
				$s->echo($destroy);
			}
		}
		//copied from Element->echo
		if($cache){
			if($print){
				Debug::print("{$f} about to update cache");
			}
			$html = ob_get_clean();
			cache()->setFile($this->getCacheKey() . ".html", $html, time() + 30 * 60);
			echo $html;
			unset($html);
		}elseif($print){
			Debug::print("{$f} nothing to cache");
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->components, $deallocate);
	}

	public function hasComponent(string $name):bool{
		return $this->hasComponents() && array_key_exists($name, $this->components);
	}

	public function setComponent(string $name, $component){
		if(!isset($this->components)){
			$this->components = [];
		}elseif($this->hasComponent($name)){
			$this->release($this->components[$name]);
		}
		return $this->components[$name] = $this->claim($component);
	}

	public function getComponent(string $component_name){
		$f = __METHOD__;
		if(!$this->hasComponents()){
			Debug::error("{$f} component \"{$component_name}\" is undefined");
		}
		return $this->components[$component_name];
	}
	
	public function echoJson(bool $destroy = false): void{
		$f = __METHOD__;
		$print = $this instanceof MultipleCheckboxes;
		if($this->getTemplateFlag()){
			Debug::print($this->__toString());
			Debug::error("{$f} should not be echoing a templated object");
		}elseif($this->hasWrapperElement()){
			$wrapper = $this->getWrapperElement();
			$this->releaseWrapperElement();
			$wrapper->appendChild($this);
			$wrapper->echoJson($destroy);
			if(!$destroy){
				ErrorMessage::unimplemented($f);
				$wrapper->removeChild($this);
				$this->setWrapperElement($wrapper);
			}else{
				$this->setSuccessors(null);
			}
			return;
		}elseif(!$this->hasParentNode()){
			$this->echoOrphan($destroy);
			return;
		}
		$this->generateContents();
		if($this->hasPredecessors()){
			$predecessors = $this->getPredecessors();
			foreach($predecessors as $p){
				$p->echoJson($destroy);
				echo ",";
			}
		}
		if(!$this->hasComponents()){
			$this->generateComponents();
		}
		$components = $this->getComponents();
		if(empty($components)){
			if($print){
				Debug::warning("{$f} components array is empty");
			}
			Json::echo("", false, false);
		}else{
			$i = 0;
			foreach($components as $component){
				if($i++ > 0){
					echo ",";
				}
				$component->echoJson($destroy);
			}
		}
		if($this->hasSuccessors()){
			$successors = $this->getSuccessors();
			$i = 0;
			foreach($successors as $s){
				if($i++ > 0){
					echo ",";
				}
				$s->echoJson($destroy);
			}
		}
	}
}
