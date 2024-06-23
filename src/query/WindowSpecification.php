<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\release;

class WindowSpecification extends Basic implements ArrayKeyProviderInterface, SQLInterface{

	use MultipleExpressionsTrait;
	use NamedTrait;
	use OrderableTrait;

	protected $frameUnits;

	protected $frameStartType;

	protected $frameStartBoundingExpression;

	protected $frameEndType;

	protected $frameEndBoundingExpression;

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->frameUnits, $deallocate);
		$this->release($this->frameEndBoundingExpression, $deallocate);
		$this->release($this->frameEndType, $deallocate);
		$this->release($this->frameStartBoundingExpression, $deallocate);
		$this->release($this->frameStartType, $deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->orderByExpression, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
	
	public final function getArrayKey(int $count){
		return $this->getName();
	}

	public function partitionBy(...$expressions):WindowSpecification{
		$this->setExpressions($expressions);
		return $this;
	}

	public function setFrameStartType($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} frame start type must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case FRAME_TYPE_CURRENT_ROW:
			case FRAME_TYPE_PRECEEDING:
			case FRAME_TYPE_FOLLOWING:
				break;
			default:
				Debug::error("{$f} invalid frame start type \"{$type}\"");
		}
		if($this->hasFrameStartType()){
			$this->release($this->frameStartType);
		}
		return $this->frameStartType = $this->claim($type);
	}

	public function hasFrameStartType():bool{
		return isset($this->frameStartType);
	}

	public function getFrameStartType(){
		$f = __METHOD__;
		if(!$this->hasFrameStartType()){
			Debug::error("{$f} frame start type is undefined");
		}
		return $this->frameStartType;
	}

	public function setFrameStartBoundingExpression($expr){
		$f = __METHOD__;
		if(!$expr instanceof ExpressionCommand){
			Debug::error("{$f} frame bounding expression must be an instanceof ExpressionCommand");
		}elseif($this->hasFrameStartBoundingExpression()){
			$this->release($this->frameStartBoundingExpression);
		}
		return $this->frameStartBoundingExpression = $this->claim($expr);
	}

	public function hasFrameStartBoundingExpression():bool{
		return isset($this->frameStartBoundingExpression);
	}

	public function getFrameStartBoundingExpression(){
		if(!$this->hasFrameStartBoundingExpression()){
			return "unbounded";
		}
		return $this->frameStartBoundingExpression;
	}

	public function getFrameStartString():string{
		$type = $this->getFrameStartType();
		if($type === FRAME_TYPE_CURRENT_ROW){
			return $type;
		}
		return $this->getFrameStartBoundingExpression() . " {$type}";
	}

	public function between(string $type, ?ExpressionCommand $expr = null):WindowSpecification{
		$this->setFrameStartType($type);
		if($expr !== null){
			$this->setFrameStartBoundingExpression($expr);
		}
		return $this;
	}

	public function setFrameEndType($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} frame start type must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case FRAME_TYPE_CURRENT_ROW:
			case FRAME_TYPE_PRECEEDING:
			case FRAME_TYPE_FOLLOWING:
				break;
			default:
				Debug::error("{$f} invalid frame start type \"{$type}\"");
		}
		if($this->hasFrameEndType()){
			$this->release($this->frameEndType);
		}
		return $this->frameEndType = $this->claim($type);
	}

	public function hasFrameEndType():bool{
		return isset($this->frameEndType);
	}

	public function getFrameEndType(){
		$f = __METHOD__;
		if(!$this->hasFrameEndType()){
			Debug::error("{$f} frame type is undefined");
		}
		return $this->frameEndType;
	}

	public function setFrameEndBoundingExpression($expr){
		$f = __METHOD__;
		if(!$expr instanceof ExpressionCommand){
			Debug::error("{$f} frame bounding expression must be an instanceof ExpressionCommand");
		}elseif($this->hasFrameEndBoundingExpression()){
			$this->release($this->frameEndBoundingExpression);
		}
		return $this->frameEndBoundingExpression = $this->claim($expr);
	}

	public function hasFrameEndBoundingExpression():bool{
		return isset($this->frameEndBoundingExpression);
	}

	public function getFrameEndBoundingExpression(){
		if(!$this->hasFrameEndBoundingExpression()){
			return "unbounded";
		}
		return $this->frameEndBoundingExpression;
	}

	public function getFrameEndString():string{
		$type = $this->getFrameEndType();
		if($type === FRAME_TYPE_CURRENT_ROW){
			return $type;
		}
		return $this->getFrameEndBoundingExpression() . " {$type}";
	}

	public function setFrameUnits($units){
		$f = __METHOD__;
		if(!is_string($units)){
			Debug::error("{$f} frame units must be a string");
		}
		$units = strtolower($units);
		switch($units){
			case FRAME_UNITS_RANGE:
			case FRAME_UNITS_ROWS:
				break;
			default:
				Debug::error("{$f} invalid frame units \"{$units}\"");
		}
		if($this->hasFrameUnits()){
			$this->release($this->frameUnits);
		}
		return $this->frameUnits = $this->claim($units);
	}

	public function and(string $type, ?ExpressionCommand $expr = null):WindowSpecification{
		$f = __METHOD__;
		if(!$this->hasFrameStartType()){
			Debug::error("{$f} don't call this function if frame start is undefined");
		}
		$this->setFrameEndType($type);
		if($expr !== null){
			$this->setFrameEndBoundingExpression($expr);
		}
		return $this;
	}

	public function hasFrameUnits():bool{
		return isset($this->frameUnits);
	}

	public function getFrameUnits(){
		$f = __METHOD__;
		if(!$this->hasFrameUnits()){
			Debug::error("{$f} frame units are undefined");
		}
		return $this->frameUnits;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			$string = "";
			// [window_name]
			// [PARTITION BY expr [, expr] ...]
			if($this->hasExpressions()){
				$ex_sql = [];
				foreach($this->getExpressions() as $e){
					if($e instanceof SQLInterface){
						$e = $e->toSQL();
					}
					array_push($ex_sql, $e);
				}
				$string .= "partition by " . implode(',', $ex_sql);
			}
			// [ORDER BY expr [ASC|DESC] [, expr [ASC|DESC]] ...]
			if($this->hasOrderBy()){
				$string .= $this->getOrderByString();
			}
			if($this->hasFrameUnits()){
				// {ROWS | RANGE}
				$string .= " " . $this->getFrameUnits() . " ";
				// {frame_start | BETWEEN frame_start AND frame_end}
				if($this->hasFrameEndType()){
					$string .= "between ";
				}
				$string .= $this->getFrameStartString();
				if($this->hasFrameEndType()){
					$string .= " and " . $this->getFrameEndString();
				}
				// frame_start, frame_end: {
				// CURRENT ROW
				// | UNBOUNDED PRECEDING
				// | UNBOUNDED FOLLOWING
				// | expr PRECEDING
				// | expr FOLLOWING
				// }
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
