<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\index\MultipleIndexNamesTrait;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\claim;

class IndexHint extends Basic implements StaticPropertyTypeInterface, SQLInterface{

	use MultipleIndexNamesTrait;
	use StaticPropertyTypeTrait;

	protected $indexHintType;

	protected $forWhat;

	public function __construct(?string $type = null){
		parent::__construct();
		// $this->requirePropertyType("indexNames", "s");
		if($type !== null){
			$this->setIndexHintType($type);
		}
	}

	public function setIndexHintType($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} index hint type must be a string");
		}elseif(empty($type)){
			Debug::error("{$f} index hint type cannot be empty string");
		}
		$type = strtolower($type);
		switch($type){
			case INDEX_HINT_TYPE_USE:
			case INDEX_HINT_TYPE_IGNORE:
			case INDEX_HINT_TYPE_FORCE:
				break;
			default:
				Debug::error("{$f} invalid index hint type \"{$type}\"");
		}
		if($this->hasIndexHintType()){
			$this->release($this->indexHintType);
		}
		return $this->indexHintType = $this->claim($type);
	}

	public function hasIndexHintType():bool{
		return isset($this->indexHintType);
	}

	public function getIndexHintType(){
		$f = __METHOD__;
		if(!$this->hasIndexHintType()){
			Debug::error("{$f} index hint type is undefined");
		}
		return $this->indexHintType;
	}

	public function setFor($what){
		$f = __METHOD__;
		
		if(!is_string($what)){
			Debug::error("{$f} index hint for variable must be a string");
		}elseif(empty($what)){
			Debug::error("{$f} empty string");
		}
		$what = strtolower($what);
		switch($what){
			case INDEX_HINT_FOR_GROUP_BY:
			case INDEX_HINT_FOR_JOIN:
			case INDEX_HINT_FOR_ORDER_BY:
				break;
			default:
				Debug::error("{$f} invalid index hint type \"{$what}\"");
		}
		if($this->hasFor()){
			$this->release($this->forWhat);
		}
		return $this->forWhat = $this->claim($what);
	}

	public function hasFor():bool{
		return isset($this->forWhat) && is_string($this->forWhat) && !empty($this->forWhat);
	}

	public function getFor(){
		$f = __METHOD__;
		if(!$this->hasFor()){
			Debug::error("{$f} for is undefined");
		}
		return $this->forWhat;
	}

	public function for($what){
		$this->setFor($what);
		return $this;
	}

	public static function useIndex():IndexHint{
		return new IndexHint(INDEX_HINT_TYPE_USE);
	}

	public static function ignoreIndex():IndexHint{
		return new IndexHint(INDEX_HINT_TYPE_IGNORE);
	}

	public static function forceIndex():IndexHint{
		return new IndexHint(INDEX_HINT_TYPE_FORCE);
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			// USE {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
			// {IGNORE|FORCE} {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
			$type = $this->getIndexHintType();
			$string = "{$type} index ";
			if($this->hasFor()){
				$string .= "for " . $this->getFor();
			}
			$string .= "(";
			if($this->hasIndexNames()){
				$string .= implode(',', $this->getIndexNames());
			}elseif($type !== INDEX_HINT_TYPE_USE){
				Debug::error("{$f} index names are requiired for ignore and force index hint types");
				return null;
			}
			$string .= ")";
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
		$this->release($this->forWhat, $deallocate);
		$this->release($this->indexHintType, $deallocate);
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"indexNames" => 's'
		];
	}
}
