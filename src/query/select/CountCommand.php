<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

/**
 * select count(*) from blah.whatever
 *
 * @author j
 *        
 */
class CountCommand extends ExpressionCommand implements SQLInterface{

	use ExpressionalTrait;
	
	public function __construct($expr = null){
		parent::__construct();
		if($expr !== null){
			$this->setExpression($expr);
		}
	}

	public static function getCommandId(): string{
		return "count";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$expr = $this->getExpression();
		if($expr instanceof ValueReturningCommandInterface){
			while($expr instanceof ValueReturningCommandInterface){
				$expr = $expr->evaluate();
			}
		}
		if(is_array($expr)){
			return count($expr);
		}elseif(is_string($expr) || $expr instanceof StringifiableInterface){
			return strlen($expr);
		}
		Debug::error("{$f} expression is neither array nor string");
	}

	public function toSQL(): string{
		$expr = $this->getExpression();
		while($expr instanceof SQLInterface){
			$expr = $expr->toSQL();
		}
		return "count({$expr})";
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasExpression()){
			$this->setExpression(replicate($that->getExpression()));
		}
		return $ret;
	}
}
