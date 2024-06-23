<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\backwards_ref_enabled;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseParentNodeEvent;

trait ParentNodeTrait{

	protected $parentNode;

	public function hasParentNode(): bool{
		return isset($this->parentNode);
	}

	/**
	 *
	 * @return object
	 */
	public function getParentNode(){
		$f = __METHOD__;
		if(!$this->hasParentNode()){
			Debug::error("{$f} parent node is undefined");
		}
		return $this->parentNode;
	}

	public function setParentNode($node){
		if($this->hasParentNode()){
			$this->releaseParentNode();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->parentNode = $node;
		}
		return $this->parentNode = $this->claim($node);
	}
	
	public function releaseParentNode(bool $deallocate=false){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasParentNode()){
			Debug::error("{$f} parent node is undefined for this ".$this->getDebugString());
		}elseif($print){
			Debug::print("{$f} about to release parent node of this ".$this->getDebugString());
		}
		$parent = $this->getParentNode();
		unset($this->parentNode);
		if($this->hasAnyEventListener(EVENT_RELEASE_PARENT)){
			$this->dispatchEvent(new ReleaseParentNodeEvent($parent, $deallocate));
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			unset($parent);
			return;
		}
		$this->release($parent, false); //$deallocate);
	}
}