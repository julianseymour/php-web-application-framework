<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use function JulianSeymour\PHPWebApplicationFramework\getRequestURISegment;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class ProgramFlowControlUnit extends Basic{
	
	public function hasImplicitParameter(string $name): bool{
		return false;
	}
	
	public function getImplicitParameter(string $name){
		$f = __METHOD__;
		Debug::error("{$f} undefined implicit parameter \"{$name}\"");
	}
	
	public function getInputParameter(string $name){
		return request()->getInputParameter($name, $this);
	}
	
	public function hasInputParameter(string $name): bool{
		return request()->hasInputParameter($name, $this);
	}
	
	public function getUriSegmentParameterMap(): ?array{
		return [
			0 => "action"
		];
	}
	
	public function URISegmentParameterExists(string $name):bool{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($this->hasImplicitParameter($name)) {
			if($print){
				Debug::print("{$f} implicit parameter \"{$name}\" exists");
			}
			return true;
		}
		$map = $this->getUriSegmentParameterMap();
		if(! isset($map) || ! is_array($map)) {
			if($print) {
				Debug::print("{$f} no input parameters are mapped to URI segments");
			}
			return false;
		}
		$offset =  array_search($name, $this->getUriSegmentParameterMap());
		if(false === $offset) {
			if($print) {
				Debug::print("{$f} parameter \"{$name}\" is not mapped to a URI segment for use case of class ".get_short_class($this));
			}
			return false;
		}elseif($offset > request()->getRequestURISegmentCount() - 1) {
			if($print) {
				Debug::print("{$f} offset {$offset} exceeds URI segment count");
			}
			return false;
		}
		$value = getRequestURISegment($offset);
		if($print) {
			Debug::print("{$f} value at offset {$offset} is \"{$value}\"");
		}
		return $value !== null && $value !== "";
	}
}