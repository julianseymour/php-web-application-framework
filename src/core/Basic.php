<?php

namespace JulianSeymour\PHPWebApplicationFramework\core;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\enable_destruct;
use function JulianSeymour\PHPWebApplicationFramework\get_file_line;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationRuntime;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StatusTrait;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;

/**
 * This class defines very generic behavior that is used by many derived classes
 *
 * @author j
 *        
 */
abstract class Basic implements DisposableInterface, HitPointsInterface{

	use EventListeningTrait;
	use FlagBearingTrait;
	use HitPointsTrait;
	use StatusTrait;

	protected $debugId;

	protected $declarationLine;

	protected $deallocationLine;
	
	protected static function getExcludedConstructorFunctionNames():?array{
		return ["__construct", "replicate"];
	}
	
	public function __construct(){
		$f = __METHOD__;
		$print = false;
		$this->setAllocatedFlag(true);
		if(DEBUG_MODE_ENABLED){
			$this->setDebugId(sha1(random_bytes(32)));
			$decl = $this->setDeclarationLine(get_file_line(static::getExcludedConstructorFunctionNames(), 7));
			if($print){
				Debug::print("{$f} constructed object with debug ID \"{$this->debugId}\" {$decl}");
			}
			global $__applicationInstance;
			if(isset($__applicationInstance) && DEBUG_REFERENCE_MAPPING_ENABLED){
				debug()->retain($this);
			}
		}
	}
	
	public function announce(HitPointsInterface $obj):void{
		Debug::printStackTraceNoExit("Instantiated ".$obj->getDebugString()." for this ".$this->getDebugString());
	}
	
	public function getDebugString():string{
		$sc = $this->getShortClass();
		$did = $this->getDebugId();
		$decl = $this->getDeclarationLine();
		return "{$sc} declared {$decl} with debug ID {$did}";
	}
	
	public function release(&$value, bool $deallocate=false){
		if($this->getDisableClaimFlag()){
			$value = null;
			return;
		}
		return release($value, $deallocate, $this->getDebugId());
	}
	
	public function claim(&$value){
		if($this->getDisableClaimFlag()){
			return $value;
		}
		return claim($value, $this->getDebugId());
	}
	
	public static function declareFlags():?array{
		return [
			"allocated",
			"debug",
			"disableClaim",
			"disableDealloc"
		];
	}
	
	public function getDisableClaimFlag():bool{
		return $this->getFlag("disableClaim");
	}
	
	public function setDisableClaimFlag(bool $value=true):bool{
		return $this->setFlag("disableClaim", $value);
	}
	
	public function disableClaim(bool $value=true){
		$this->setDisableClaimFLag($value);
		return $this;
	}
	
	public function setDisableDeallocationFlag(bool $value=true):bool{
		return $this->setFlag("disableDealloc", $value);
	}
	
	public function getDisableDeallocationFlag():bool{
		return $this->getFlag("disableDealloc");
	}
	
	public function disableDeallocation(bool $value=true):Basic{
		$this->setDisableDeallocationFlag($value);
		return $this;
	}
	
	public function enableDeallocation():Basic{
		$this->setDisableDeallocationFlag(false);
		return $this;
	}
	
	public function setAllocatedFlag(bool $value = true):bool{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag() && !$this instanceof ApplicationRuntime;
		if($print){
			if($value){
				Debug::print("{$f} setting allocated flag for this ".$this->getDebugString());
			}elseif($this->getAllocatedFlag()){
				Debug::printStackTraceNoExit("{$f} unsetting allocated flag for this ".$this->getDebugString());
			}
		}
		return $this->setFlag("allocated", $value);
	}

	public function getAllocatedFlag():bool{
		return $this->getFlag("allocated");
	}

	public function setDeclarationLine($dl):?string{
		$f = __METHOD__;
		if($dl == null){
			unset($this->declarationLine);
			return null;
		}
		return $this->declarationLine = $dl;
	}

	public function hasDeclarationLine():bool{
		return isset($this->declarationLine);
	}

	public function getDeclarationLine():string{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasDeclarationLine()){
			if($print){
				Debug::warning("{$f} declaration line is undefined");
			}
			return "undefined";
		}
		return $this->declarationLine;
	}

	public function getDebugFlag(): bool{
		return $this->getFlag("debug");
	}

	public function setDebugFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if($value && $this->getDebugFlag()){
			Debug::error("{$f} debug flag is already set");
		}elseif($print){
			if($value){
				Debug::printStackTraceNoExit("{$f} setting debug flag for ".$this->getDebugString());
			}else{
				Debug::printStackTraceNoExit("{$f} unsetting debug flag for ".$this->getDebugString());
			}
		}
		return $this->setFlag("debug", $value);
	}

	public function debug(bool $value = true): Basic{
		$f = __METHOD__;
		$this->setDebugFlag($value);
		return $this;
	}

	protected function setDebugId(?string $id):?string{
		return $this->debugId = $id;
	}

	public static final function getClass():string{
		return static::class;
	}

	public function __destruct(){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		global $__destructDisabled;
		if($__destructDisabled){
			if($this->getAllocatedFlag()){
				if($print && $this->getDebugFlag()){
					Debug::warning("{$f} __destruct functions are currently disabled. This might be caused by setting the __destructDisabled global variable to true, or it could be due to a bug in PHP 8.1 that randomly garbage collects objects in certain situations (calling debug_backtrace seems to cause it frequently). This object is a ".$this->getDebugString());
				}/*else{
					global $__error;
					if(!$__error){
						Debug::printStackTraceNoExit("{$f} premature destructor function call for ".$this->getDebugString());
					}
				}*/
			}
		}else{
			if(SUPPLEMENTAL_GARBAGE_COLLECTION_ENABLED && $this->getDebugFlag() && $this->getAllocatedFlag()){
				Debug::warning("{$f} premature destructor function called for this ".$this->getDebugString());
				if(DEBUG_MODE_ENABLED && DEBUG_REFERENCE_MAPPING_ENABLED){
					debug()->printClaimants($this);
				}
				Debug::printStackTrace();
			}
			$this->dispose(false);
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag() && !$this instanceof ApplicationRuntime;
		if($print){
			$ds = $this->getDebugString();
			if($deallocate){
				Debug::print("{$f} entered. Hard deallocating for this {$ds}");
			}else{
				Debug::print("{$f} skipping hard deallocation for this {$ds}");
				if($this->getHitPoints() > 0){
					Debug::warning("{$f} unfortunately, this object was not fully deallocated. Printing its claimants now.");
					debug()->printClaimants($this, 50);
				}else{
					Debug::print("{$f} hit points are zero");
				}
			}
		}
		$this->setAllocatedFlag(false);
		//$this->dispatchEvent(new DisposeEvent()); //causes segmentation fault
		if(
			$deallocate &&
			DEBUG_MODE_ENABLED &&
			DEBUG_REFERENCE_MAPPING_ENABLED && 
			$this->hasDebugId() && 
			debug()->has($this->debugId)
		){
			debug()->remove($this->debugId);
		}
		//$this->release($this->debugId, $deallocate);
		//$this->release($this->declarationLine, $deallocate);
		//$this->release($this->eventListeners, $deallocate);
		//$this->release($this->flags, $deallocate);
		$this->release($this->status, $deallocate);
		//$this->release($this->undeclaredFlags, $deallocate);
	}

	public function hasDebugId():bool{
		return isset($this->debugId);
	}

	public function getDebugId():string{
		if(!$this->hasDebugId()){
			return "[undefined]";
		}
		return $this->debugId;
	}

	public static function getShortClass(): string{
		return get_short_class(static::class);
	}
	
	public function hasDeallocationLine():bool{
		return isset($this->deallocationLine);
	}
	
	public function getDeallocationLine():string{
		return $this->hasDeallocationLine() ? $this->deallocationLine : "[undefined]";
	}
	
	public function setDeallocationLine(?string $line):?string{
		if($line === null){
			unset($this->deallocationLine);
			return null;
		}
		return $this->deallocationLine = $line;
	}
	
	public static function getCopyableFlags():?array{
		return [];
	}
	
	public function copy($that):int{
		foreach($this->getCopyableFlags() as $name){
			$flag = $that->getFlag($name);
			if($flag){
				$this->setFlag($name, $flag);
			}
		}
		return SUCCESS;
	}
}
