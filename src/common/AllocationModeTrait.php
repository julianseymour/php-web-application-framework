<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\claim;

trait AllocationModeTrait{
	
	/**
	 * Specifies when this element's contents should be generated.
	 * Set with setAllocationMode(); get with getAllocationMode().
	 * Possible values include:
	 * ALLOCATION_MODE_EAGER : generate child nodes immediately
	 * ALLOCATION_MODE_LAZY : defer child node generation until before the node is about to be echoed
	 * ALLOCATION_MODE_NEVER : do not generate child nodes
	 * ALLOCATION_MODE_FORM : generate inputs only for a form that is getting processed but not rendered or converted to array (except possibly as a subordinate form)
	 * ALLOCATION_MODE_TEMPLATE : for generating a javascript function that creates the element client side
	 * ALLOCATION_MODE_EMAIL : for elements getting rendered as part of inline HTML email. This has implications mostly for attached images.
	 * ALLOCATION_MODE_DOMPDF_COMPATIBLE : element is to be rendered by dompdf, which does not support many element tags
	 * Unimplemented values:
	 * ALLOCATION_MODE_ULTRA_LAZY : Immediately echo appended child nodes as HTML or JSON. Note this has a bug that I don't intend to address where if an element calls appendChild inside of generatePredecessors, and also has elements to append in generateChildNodes, only the nodes appended in generatePredecessors will actually get appended
	 *
	 * @var ?int
	 */
	protected $allocationMode;
	
	public abstract function getDeclarationLine();
	
	public function setAllocationMode(?int $mode): ?int{
		$f = __METHOD__;
		$print = false;
		if(!is_int($mode)){
			$gottype = is_object($mode) ? $mode->getClass() : gettype($mode);
			Debug::error("{$f} allocation mode must be an integer, {$gottype} received");
		}elseif($print){
			$decl = $this->getDeclarationLine();
			Debug::printStackTraceNoExit("{$f} setting allocation mode to \"{$mode}\". Declared {$decl}");
		}
		if($this->hasAllocationMode()){
			$this->release($this->allocationMode);
		}
		
		return $this->allocationMode = $this->claim($mode);
	}
	
	public function hasAllocationMode(): bool{
		return isset($this->allocationMode) 
		&& is_int($this->allocationMode) 
		&& $this->allocationMode !== ALLOCATION_MODE_UNDEFINED;
	}
	
	public function getAllocationMode(): int{
		if(!$this->hasAllocationMode()){
			return ALLOCATION_MODE_UNDEFINED;
		}
		return $this->allocationMode;
	}
}
