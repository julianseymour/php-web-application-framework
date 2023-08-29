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

class WindowSpecification extends Basic implements ArrayKeyProviderInterface, SQLInterface
{

	use MultipleExpressionsTrait;
	use NamedTrait;
	use OrderableTrait;

	protected $frameUnits;

	protected $frameStartType;

	protected $frameStartBoundingExpression;

	protected $frameEndType;

	protected $frameEndBoundingExpression;

	public final function getArrayKey(int $count)
	{
		return $this->getName();
	}

	public function partitionBy(...$expressions)
	{
		$this->setExpressions($expressions);
		return $this;
	}

	public function setFrameStartType($type)
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->setFrameStartType()";
		if ($type == null) {
			unset($this->frameStartType);
			return null;
		} elseif (! is_string($type)) {
			Debug::error("{$f} frame start type must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case FRAME_TYPE_CURRENT_ROW:
			case FRAME_TYPE_PRECEEDING:
			case FRAME_TYPE_FOLLOWING:
				return $this->frameStartType = $type;
			default:
		}
		Debug::error("{$f} invalid frame start type \"{$type}\"");
	}

	public function hasFrameStartType()
	{
		return isset($this->frameStartType);
	}

	public function getFrameStartType()
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->getFrameStartType()";
		if (! $this->hasFrameStartType()) {
			Debug::error("{$f} frame start type is undefined");
		}
		return $this->frameStartType;
	}

	public function setFrameStartBoundingExpression($expr)
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->setFrameStartBoundingExpression()";
		if ($expr == null) {
			unset($this->frameStartBoundingExpression);
			return null;
		} elseif (! $expr instanceof ExpressionCommand) {
			Debug::error("{$f} frame bounding expression must be an instanceof ExpressionCommand");
		}
		return $this->frameStartBoundingExpression;
	}

	public function hasFrameStartBoundingExpression()
	{
		return isset($this->frameStartBoundingExpression);
	}

	public function getFrameStartBoundingExpression()
	{
		if (! $this->hasFrameStartBoundingExpression()) {
			return "unbounded";
		}
		return $this->frameStartBoundingExpression;
	}

	public function getFrameStartString()
	{
		$type = $this->getFrameStartType();
		if ($type === FRAME_TYPE_CURRENT_ROW) {
			return $type;
		}
		return $this->getFrameStartBoundingExpression() . " {$type}";
	}

	public function between(string $type, ?ExpressionCommand $expr = null)
	{
		$this->setFrameStartType($type);
		if ($expr !== null) {
			$this->setFrameStartBoundingExpression($expr);
		}
		return $this;
	}

	public function setFrameEndType($type)
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->setFrameEndType()";
		if ($type == null) {
			unset($this->frameEndType);
			return null;
		} elseif (! is_string($type)) {
			Debug::error("{$f} frame start type must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case FRAME_TYPE_CURRENT_ROW:
			case FRAME_TYPE_PRECEEDING:
			case FRAME_TYPE_FOLLOWING:
				return $this->frameEndType = $type;
			default:
		}
		Debug::error("{$f} invalid frame start type \"{$type}\"");
	}

	public function hasFrameEndType()
	{
		return isset($this->frameEndType);
	}

	public function getFrameEndType()
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->getFrameEndType()";
		if (! $this->hasFrameEndType()) {
			Debug::error("{$f} frame type is undefined");
		}
		return $this->frameEndType;
	}

	public function setFrameEndBoundingExpression($expr)
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->setFrameEndBoundingExpression()";
		if ($expr == null) {
			unset($this->frameEndBoundingExpression);
			return null;
		} elseif (! $expr instanceof ExpressionCommand) {
			Debug::error("{$f} frame bounding expression must be an instanceof ExpressionCommand");
		}
		return $this->frameEndBoundingExpression;
	}

	public function hasFrameEndBoundingExpression()
	{
		return isset($this->frameEndBoundingExpression);
	}

	public function getFrameEndBoundingExpression()
	{
		if (! $this->hasFrameEndBoundingExpression()) {
			return "unbounded";
		}
		return $this->frameEndBoundingExpression;
	}

	public function getFrameEndString()
	{
		$type = $this->getFrameEndType();
		if ($type === FRAME_TYPE_CURRENT_ROW) {
			return $type;
		}
		return $this->getFrameEndBoundingExpression() . " {$type}";
	}

	public function setFrameUnits($units)
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->setFrameUnits()";
		if ($units == null) {
			unset($this->frameUnits);
			return null;
		} elseif (! is_string($units)) {
			Debug::error("{$f} frame units must be a string");
		}
		$units = strtolower($units);
		switch ($units) {
			case FRAME_UNITS_RANGE:
			case FRAME_UNITS_ROWS:
				return $this->frameUnits = $units;
			default:
		}
		Debug::error("{$f} invalid frame units \"{$units}\"");
	}

	public function and(string $type, ?ExpressionCommand $expr = null)
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->and()";
		if (! $this->hasFrameStartType()) {
			Debug::error("{$f} don't call this function if frame start is undefined");
		}
		$this->setFrameEndType($type);
		if ($expr !== null) {
			$this->setFrameEndBoundingExpression($expr);
		}
		return $this;
	}

	public function hasFrameUnits()
	{
		return isset($this->frameUnits) && $this->hasFrameStartType();
	}

	public function getFrameUnits()
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->getFrameUnits()";
		if (! $this->hasFrameUnits()) {
			Debug::error("{$f} frame units are undefined");
		}
		return $this->frameUnits;
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //WindowSpecification::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			$string = "";
			// [window_name]
			// [PARTITION BY expr [, expr] ...]
			if ($this->hasExpressions()) {
				$ex_sql = [];
				foreach ($this->getExpressions() as $e) {
					if ($e instanceof SQLInterface) {
						$e = $e->toSQL();
					}
					array_push($ex_sql, $e);
				}
				$string .= "partition by " . implode(',', $ex_sql);
			}
			// [ORDER BY expr [ASC|DESC] [, expr [ASC|DESC]] ...]
			if ($this->hasOrderBy()) {
				$string .= $this->getOrderByString();
			}
			if ($this->hasFrameUnits()) {
				// {ROWS | RANGE}
				$string .= " " . $this->getFrameUnits() . " ";
				// {frame_start | BETWEEN frame_start AND frame_end}
				if ($this->hasFrameEndType()) {
					$string .= "between ";
				}
				$string .= $this->getFrameStartString();
				if ($this->hasFrameEndType()) {
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
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->frameUnits);
		unset($this->frameEndBoundingExpression);
		unset($this->frameEndType);
		unset($this->frameStartBoundingExpression);
		unset($this->frameStartType);
		unset($this->name);
	}
}
