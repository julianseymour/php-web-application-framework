<?php

namespace JulianSeymour\PHPWebApplicationFramework\core;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\VisitedFlagTrait;

class Node{
	
	use FlagBearingTrait;
	use ValuedTrait;
	use VisitedFlagTrait;
	
	protected $edges;
	
	public function __construct($value=null){
		if($value !== null){
			$this->setValue($value);
		}
	}
	
	public static function declareFlags():?array{
		return [
			"visited" //for navigating a graph of nodes without infinite loop
		];
	}
	
	public function setValue($value){
		return $this->value = $value;
	}
	
	public function hasEdges():bool{
		return isset($this->edges) && is_array($this->edges) && !empty($this->edges);
	}
	
	public function getEdges():array{
		$f = __METHOD__;
		if(!$this->hasEdges()){
			Debug::error("{$f} edges are undefined");
		}
		return $this->edges;
	}
	
	public function hasEdge($key):bool{
		return isset($this->edges) && is_array($this->edges) && array_key_exists($key, $this->edges);
	}
	
	public function getEdge($key){
		$f = __METHOD__;
		if(!$this->hasEdge($key)){
			Debug::error("{$f} edge \"{$key}\" is undefined");
		}
		return $this->edges[$key];
	}
	
	public function setEdge($key, $value){
		if(!isset($this->edges) || !is_array($this->edges)){
			$this->edges = [];
		}
		return $this->edges[$key] = $value;
	}
	
	public function removeEdge($key){
		$f = __METHOD__;
		if(!$this->hasEdge($key)){
			Debug::error("{$f} edge \"{$key}\" is undefined");
		}
		unset($this->edges[$key]);
		if(empty($this->edges)){
			unset($this->edges);
		}
	}
	
	public function __destruct(){
		//unset($this->edges);
		unset($this->flags);
		unset($this->undeclaredFlags);
		//unset($this->value);
	}
	
	public function getEdgeCount():int{
		if(!$this->hasEdges()){
			return 0;
		}
		return count($this->edges);
	}
}
