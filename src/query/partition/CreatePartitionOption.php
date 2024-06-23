<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\partition;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use Exception;

class CreatePartitionOption extends Basic implements SQLInterface{

	use ExpressionalTrait;
	use MultipleColumnNamesTrait;

	protected $algorithm;

	//protected $linearity;

	protected $partitionCount;

	protected $partitionType;

	// protected $subpartition;
	
	public function __construct($type){
		parent::__construct();
		$this->requireArrayProperyType("columnNames", 's');
		$this->setPartitonType($type);
	}

	public function setPartitionCount($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} partition count must be a positive integer");
		}elseif($count <= 0){
			Debug::error("{$f} partition count must be positive");
		}elseif($this->hasPartitionCount()){
			$this->release($this->partitionCount);
		}
		return $this->partitionCount = $this->claim($count);
	}

	public function hasPartitionCount():bool{
		return isset($this->partitionCount); //&& is_int($this->partitionCount) && $this->partitionCount > 0;
	}

	public function getPartitionCount(){
		$f = __METHOD__;
		if(!$this->hasPartitionCount()){
			Debug::error("{$f} partition count is undefined");
		}
		return $this->partitionCount;
	}

	public function partitions($count){
		$this->setPartitionCount($count);
		return $this;
	}

	public function subpartitions($count){
		return $this->partitions($count);
	}

	public static function key($columnNames): CreatePartitionOption{
		return (new CreatePartitionOption(PARTITION_TYPE_KEY))->withColumnNames($columnNames);
	}

	public function algorithm($alg): CreatePartitionOption{
		$this->setAlgorithm($alg);
		return $this;
	}

	public function linear(bool $value=true): CreatePartitionOption{
		$this->setLinearFlag($value);
		return $this;
	}

	public static function linearKey($columnNames): CreatePartitionOption{
		return static::key($columnNames)->linear();
	}

	public static function linearHash($expression): CreatePartitionOption{
		return static::hash($expression)->linear();
	}

	public static function hash($expression): CreatePartitionOption{
		return (new CreatePartitionOption(PARTITION_TYPE_HASH))->withExpression($expression);
	}

	private static function listOrRange($partitionType, $expression_or_columnNames): CreatePartitionOption{
		$f = __METHOD__;
		$option = new CreatePartitionOption($partitionType);
		if($expression_or_columnNames === null){
			return $option;
		}elseif($expression_or_columnNames instanceof ValueReturningCommandInterface){
			return $option->withExpression($expression_or_columnNames);
		}elseif(is_array($expression_or_columnNames)){
			return $option->withColumnNames($expression_or_columnNames);
		}
		Debug::error("{$f} none of the above");
	}

	public static function list($expression_or_columnNames): CreatePartitionOption{
		return static::listOrRange(PARTITION_TYPE_LIST, $expression_or_columnNames);
	}

	public static function range($expression_or_columnNames): CreatePartitionOption{
		return static::listOrRange(PARTITION_TYPE_RANGE, $expression_or_columnNames);
	}

	public function setAlgorithm($alg){
		$f = __METHOD__;
		if(!is_int($alg) || ($alg !== 1 && ! $alg !== 2)){
			Debug::error("{$f} this function accepts only the integers 1 and 2");
		}elseif($this->hasAlgorithm()){
			$this->release($this->algorithm);
		}
		return $this->algorithm = $this->claim($alg);
	}

	public function hasAlgorithm():bool{
		return isset($this->algorithm); //&& is_int($this->algorithm) && ($this->algorithm === 1 || $this->algorithm === 2);
	}

	public function getAlgorithm(){
		$f = __METHOD__;
		if(!$this->hasAlgorithm()){
			Debug::error("{$f} algorithm is undefined");
		}
		return $this->algorithm;
	}

	public function setLinearFlag(bool $linearity=true):bool{
		return $this->setFlag("linear", $linearity);
	}
	
	/*public function setLinearity($linearity){
		if(!is_bool($linearity)){
			$linearity = boolval($linearity);
		}
		return $this->linearity = $linearity;
	}

	public function hasLinearity():bool{
		return $this->getFLag("linear"); //isset($this->linearity) && is_bool($this->linearity);
	}

	public function getLinearity(){
		$f = __METHOD__;
		if(!$this->hasLinearity()){
			Debug::error("{$f} linearity is undefined");
		}
		return $this->linearity;
	}

	public function isLinear():bool{
		return $this->getLinearity() === true;
	}*/
	
	public function getLinearFlag():bool{
		return $this->getFlag("linear");
	}

	public function setPartitonType($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} partition type is not a string");
		}
		switch($type){
			case PARTITION_TYPE_HASH:
			case PARTITION_TYPE_KEY:
			case PARTITION_TYPE_LIST:
			case PARTITION_TYPE_RANGE:
				break;
			default:
				Debug::error("{$f} invalid partition type \"{$type}\"");
		}
		if($this->hasPartitionType()){
			$this->release($this->partitionType);
		}
		return $this->partitionType = $this->claim($type);
	}

	public function hasPartitionType():bool{
		return isset($this->partitionType);
	}

	public function getPartitionType(){
		$f = __METHOD__;
		if(!$this->hasPartitionType()){
			Debug::error("{$f} partition type is undefined");
		}
		return $this->partitionType;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			$type = $this->getPartitionType();
			if(($type === PARTITION_TYPE_HASH || $type === PARTITION_TYPE_KEY) && $this->getLinearFlag()){
				$string = "linear {$type}";
			}else{
				$string = $type;
			}
			if($type === PARTITION_TYPE_KEY){
				// [LINEAR] KEY [ALGORITHM={1 | 2}] (column_list)
				if($this->hasAlgorithm()){
					$string .= " algorithm=" . $this->getAlgorithm();
				}
				$string .= " (" . $this->getColumnNameString() . ")";
			}elseif($type === PARTITION_TYPE_HASH){
				// [LINEAR] HASH(expr)
				$string .= "(" . $this->getExpression() . ")";
			}else{
				// RANGE{(expr) | COLUMNS(column_list)}
				// LIST{(expr) | COLUMNS(column_list)}
				if($this->hasExpression()){
					$string .= "(" . $this->getExpression() . ")";
				}elseif($this->hasColumnNames()){
					$string .= " columns(" . $this->getColumnNameString() . ")";
				}else{
					Debug::error("{$f} neither of the above");
				}
			}
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
		$this->release($this->algorithm, $deallocate);
		$this->release($this->expression, $deallocate);
		//$this->release($this->linearity, $deallocate);
		$this->release($this->partitionCount, $deallocate);
		$this->release($this->partitionType, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
}
