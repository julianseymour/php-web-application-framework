<?php

namespace JulianSeymour\PHPWebApplicationFramework\core;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\use_case;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;

class Debugger extends Debug{

	use FlagBearingTrait;

	protected $counter;
	
	protected $digested;

	protected $enforcedPrivateKey;

	protected $logNumber;
	
	protected $logs;
	
	protected $nodes;
	
	protected $string;
	
	public function startLog(){
		if(!isset($this->logNumber)){
			$this->logNumber = 0;
		}else{
			$this->logNumber++;
		}
		if(!is_array($this->logs)){
			$this->logs = [ [] ];
		}else{
			array_push($this->logs, []);
		}
	}
	
	public function disableLog(){
		$this->logs = array_slice($this->logs, 0, $this->logNumber);
		$this->logNumber--;
		if($this->logNumber < 0){
			unset($this->logNumber);
			unset($this->logs);
		}
	}
	
	public function log(string $info):void{
		$f = __METHOD__;
		if(!$this->isLogEnabled()){
			Debug::error("{$f} logging is disabled");
		}
		array_push($this->logs[$this->logNumber], $info);
	}
	
	public function dumpLog(){
		$f = __METHOD__;
		if(!$this->isLogEnabled()){
			Debug::error("{$f} logging is disabled");
		}
		unset($_SESSION['debug_log']);
		foreach($this->logs[$this->logNumber] as $i => $log){
			Debug::print($log);
		}
		$this->logs = array_slice($this->logs, 0, $this->logNumber);
		$this->logNumber--;
		if($this->logNumber < 0){
			unset($this->logNumber);
			unset($this->logs);
		}else{
			$_SERVER['debug_log'] = true;
		}
	}
	
	public static function declareFlags(): array{
		return [
			"forbidGuest"
		];
	}

	public function hasEnforcedPrivateKey(): bool{
		return isset($this->enforcedPrivateKey);
	}

	public function setEnforcedPrivateKey(?string $key): ?string{
		unset($this->enforcedPrivateKey);
		return $this->enforcedPrivateKey = $key;
	}

	public function getEnforcedPrivateKey(): string{
		$f = __METHOD__;
		if(!$this->hasEnforcedPrivateKey()){
			Debug::error("{$f} enforced private key is undefined");
		}
		return $this->enforcedPrivateKey;
	}

	public function addClaimant($key, $claimant):int{
		$f = __METHOD__;
		if(!DEBUG_MODE_ENABLED || !DEBUG_REFERENCE_MAPPING_ENABLED){
			Debug::error("{$f} debug reference mapping mode must be enabled and audit memory flag must be set to use this feature");
		}elseif($key instanceof HitPointsInterface){
			$key = $key->getDebugId();
		}
		if(!$this->has($key)){
			Debug::error("{$f} node \"{$key}\" is undefined");
		}
		$node = $this->nodes[$key];
		$count = 1;
		if($node->hasEdge($claimant)){
			$count += $node->getEdge($claimant);
		}
		return $node->setEdge($claimant, $count);
	}
	
	public function removeClaimant($key, $claimant):int{
		$f = __METHOD__;
		if(!DEBUG_MODE_ENABLED || !DEBUG_REFERENCE_MAPPING_ENABLED){
			Debug::error("{$f} debug reference mapping mode must be enabled and audit memory flag must be set to use this feature");
		}elseif($key instanceof HitPointsInterface){
			$key = $key->getDebugId();
		}
		if(!$this->has($key)){
			Debug::printStackTraceNoExit("{$f} node \"{$key}\" is undefined");
			return -1;
		}
		$node = $this->nodes[$key];
		if(!$node->hasEdge($claimant)){
			Debug::error("{$f} node {$key} does not have an edge \"{$claimant}\"");
		}
		$count = $node->getEdge($claimant);
		if($count === 1){
			$node->removeEdge($claimant);
			return 0;
		}
		return $node->setEdge($claimant, $count - 1);
	}
	
	public function setString(?string $s):?string{
		return $this->string = $s;
	}
	
	public function hasString():bool{
		return isset($this->string);
	}
	
	public function getString():?string{
		$f = __METHOD__;
		if(!$this->hasString()){
			Debug::error("{$f} string is undefined");
		}
		return $this->string;
	}
	
	public function enforcePrivateKey(?string $key): Debugger{
		$f = __METHOD__;
		if(!$this->hasEnforcedPrivateKey()){
			$this->setEnforcedPrivateKey($key);
			Debug::print("{$f} enforcing private key with hash " . sha1($key));
		}elseif($key !== $this->getEnforcedPrivateKey()){
			Debug::error("{$f} illegal key with hash " . sha1($key));
		}
		return $this;
	}

	public function digest(string $s): int{
		if(!isset($this->digested) || !is_array($this->digested)){
			$this->digested = [];
		}
		array_push($this->digested, $s);
		return count($this->digested);
	}

	public function spew(): void{
		$f = __METHOD__;
		if(isset($this->digested) && is_array($this->digested) && !empty($this->digested)){
			foreach($this->digested as $i => $l){
				Debug::print("#{$i}: {$l}");
			}
		}
		$string = "";
		global $__applicationInstance;
		if(isset($__applicationInstance)){
			if(app()->hasUserData()){
				$user = user();
				$uc = $user->getClass();
				$key = $user->hasIdentifierValue() ? $user->getIdentifierValue() : "[undefined]";
				$string .= "user is a {$uc} with key \"{$key}\".";
			}else{
				$string .= "user data is undefined.";
			}
			if(app()->hasUseCase()){
				$uc = use_case();
				$string .= " Use case is a ".$uc->getShortClass()." declared ".$uc->getDeclarationLine();
			}else{
				$string .= " No use case.";
			}
		}else{
			$string .= " ApplicationRuntime is undefined.";
		}
		if(DEBUG_MODE_ENABLED && DEBUG_REFERENCE_MAPPING_ENABLED){
			$string .= " Instantiated {$this->counter} objects";
		}
		Debug::print("{$f} {$string}");
		Debug::printStackTraceNoExit();
	}
	
	public function retain(...$objects){
		$f = __METHOD__;
		$print = false;
		if(!isset($objects) || count($objects) < 1){
			Debug::error("{$f} invalid input parameters");
		}elseif($print){
			Debug::print("{$f} entered with ".count($objects)." new objects");;
		}
		if(!isset($this->nodes) || !is_array($this->nodes)){
			$this->nodes = [];
		}
		foreach($objects as $o){
			$this->nodes[$o->getDebugId()] = new Node($o->getDebugString());
			if(!isset($this->counter) || !is_int($this->counter)){
				$this->counter = 1;
			}else{
				$this->counter++;
			}
		}
		if($print){
			Debug::print("{$f} returning normally with an updated object count of {$this->counter}");
		}
	}
	
	public function remove(...$keys){
		$f = __METHOD__;
		$print = false;
		if(!isset($keys) || count($keys) < 1){
			Debug::error("{$f} invalid input parameters");
		}
		foreach($keys as $key){
			if(is_object($key)){
				$key = $key->getDebugId();
			}
			if(array_key_exists($key, $this->nodes)){
				if($print){
					Debug::print("{$f} removing an object with key {$key}");
				}
				unset($this->nodes[$key]);
			}else{
				Debug::print("{$f} we don't know about an object with debug ID \"{$key}\"");
			}
		}
	}
	
	public function getObjectDescription(string $key):string{
		if($this->has($key)){
			return $this->nodes[$key]->getValue();
		}
		return "unknown object {$key}";
	}
	
	public function printClaimants($that, ?int $limit=null){
		$f = __METHOD__;
		if(!DEBUG_MODE_ENABLED || !DEBUG_REFERENCE_MAPPING_ENABLED){
			Debug::error("{$f} debug reference mapping mode must be enabled to use this feature");
		}elseif($that instanceof Node){
			$node = $that;
		}else{
			if($that instanceof HitPointsInterface){
				$did = $that->getDebugId();
				if(!$this->has($did)){
					Debug::warning("{$f} debugger does not know about an object with debug ID \"{$did}\"");
					return;
				}
			}elseif(is_string($that) || is_int($that)){
				$did = $that;
			}
			$node = $this->nodes[$did];
		}
		if(!$node->hasValue()){
			Debug::error("{$f} node has no value");
		}
		$ds = $node->getValue();
		if(!$node->hasEdges()){
			Debug::print("{$f} no claimant IDs for node {$ds}");
			return;
		}
		$node->setVisitedFlag();
		$count = $node->getEdgeCount();
		Debug::print("{$f} {$count} claimants for this {$ds}");
		$printed = 0;
		foreach($node->getEdges() as $id => $num){
			if(!$this->has($id)){
				Debug::print("{$f} debugger doesn't know about incoming neighbor \"{$id}\"");
				continue;
			}
			$neighbor = $this->nodes[$id];
			$desc = $neighbor->getValue();
			$s = "Claimant {$desc}";
			if($num > 1){
				$s .= " ({$num} claims)";
			}
			Debug::print($s);
			if($limit !== null && $printed++ === $limit){
				Debug::print(($count - $limit)." claimants not shown");
				break;
			}
		}
		$printed = 0;
		foreach($node->getEdges() as $id => $num){
			if(!$this->has($id)){
				Debug::print("{$f} debugger doesn't know about an object with ID {$id}");
				continue;
			}
			$claimant = $this->nodes[$id];
			if($claimant->getVisitedFlag()){
				Debug::print("{$f} cycle detected between {$ds} and ".$claimant->getValue());
				if($limit !== null && $printed++ === $limit){
					break;
				}
				continue;
			}
			$this->printClaimants($claimant, $limit);
			if($limit !== null && $printed++ === $limit){
				break;
			}
		}
		$node->setVisitedFlag(false);
	}
	
	public function audit(?int $limit=null):void{
		$f = __METHOD__;
		if(!isset($this->nodes) || !is_array($this->nodes) || empty($this->nodes)){
			return;
		}
		$c = 0;
		foreach($this->nodes as $o){
			$c++;
			$desc = $o->getValue();
			Debug::print("#{$c}: {$desc}");
			if($limit !== null && $c === $limit){
				Debug::print((count($this->nodes) - $limit)." objects now shown");
				break;
			}
		}
		Debug::print("{$f} ".count($this->nodes)." of {$this->counter} objects were not properly disposed");
		Debug::checkMemoryUsage("Memory audit", 112000000, true);
	}
	
	public function has($key):bool{
		if(!isset($this->nodes) || !is_array($this->nodes) || empty($this->nodes)){
			return false;
		}elseif(is_object($key)){
			$key = $key->getDebugId();
		}
		return array_key_exists($key, $this->nodes);
	}
	
	public function get($key):Node{
		$f = __METHOD__;
		if(!$this->has($key)){
			Debug::error("{$f} node \"{$key}\" is undefined");
		}
		return $this->nodes[$key];
	}
	
	public function __destruct(){
		unset($this->counter);
		unset($this->enforcedPrivateKey);
		unset($this->digested);
		unset($this->nodes);
		unset($this->string);
	}
}
