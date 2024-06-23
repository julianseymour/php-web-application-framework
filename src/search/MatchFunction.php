<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParameterCountingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;
use Exception;

class MatchFunction extends ExpressionCommand implements StringifiableInterface, WhereConditionalInterface{

	use ExpressionalTrait;
	use MultipleColumnNamesTrait;
	use ParameterCountingTrait;

	protected $searchModifier;

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasExpression()){
			$this->setExpression(replicate($that->getExpression()));
		}
		if($that->hasColumnNames()){
			$this->setColumnNames(replicate($that->getColumnNames()));
		}
		if($that->hasParameterCount()){
			$this->setParameterCount(replicate($that->getParameterCount()));
		}
		if($that->hasSearchModifier()){
			$this->setSearchModifier(replicate($that->getSearchModifier()));
		}
		return $ret;
	}
	
	public function setSearchModifier($sm){
		$f = __METHOD__;
		if(!is_string($sm)){
			Debug::error("{$f} search modifier must be a string");
		}elseif(empty($sm)){
			Debug::error("{$f} empty string");
		}
		$sm = strtolower($sm);
		switch($sm){
			case SEARCH_MODIFIER_BOOLEAN_MODE:
			case SEARCH_MODIFIER_NATURAL_LANGUAGE_MODE:
			case SEARCH_MODIFIER_NATURAL_LANGUAGE_QUERY_EXPANSION:
			case SEARCH_MODIFIER_QUERY_EXPANSION:
				break;
			default:
				Debug::error("{$f} invalid search modifier \"{$sm}\"");
		}
		if($this->hasSearchModifier()){
			$this->release($this->searchModifier);
		}
		return $this->searchModifier = $this->claim($sm);
	}

	public function hasSearchModifier():bool{
		return isset($this->searchModifier) && is_string($this->searchModifier) && !empty($this->searchModifier);
	}

	public function getSearchModifier(){
		$f = __METHOD__;
		if(!$this->hasSearchModifier()){
			Debug::error("{$f} search modifier is undefined");
		}
		return $this->searchModifier;
	}

	public function withSearchModifier($sm): MatchFunction{
		$this->setSearchModifier($sm);
		return $this;
	}

	public function getExpression(){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasExpression()){
			if(!$this->hasParameterCount()){
				Debug::error("{$f} parameter count is undefined");
			}elseif($print){
				Debug::print("{$f} expression is undefined; auto generating one from parameter count");
			}
			$count = $this->getParameterCount();
			if($print){
				Debug::print("{$f} parameter count {$count}");
			}
			$string = '?';
			if($count == 1){
				return $string;
			}
			$string = str_pad($string, (2 * $count) - 1, ",?");
			if($print){
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}elseif($print){
			Debug::print("{$f} expression was already defined");
		}
		return $this->expression;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			// MATCH (col1,col2,...)
			$string = "match (" . implode_back_quotes(',', $this->getColumnNames()) . ") ";
			// AGAINST (expr [search_modifier])
			$expression = $this->getExpression();
			if($expression instanceof SQLInterface){
				$expression = $expression->toSQL();
			}
			$string .= "against ({$expression}";
			// search_modifier:{
			// IN NATURAL LANGUAGE MODE
			// | IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION
			// | IN BOOLEAN MODE
			// | WITH QUERY EXPANSION
			// }
			if($this->hasSearchModifier()){
				$string .= $this->getSearchModifier();
			}
			$string .= ")";
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->parameterCount, $deallocate);
		$this->release($this->searchModifier, $deallocate);
	}

	public static function getCommandId(): string{
		return "match";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getFlatWhereConditionArray(): ?array{
		return [
			$this
		];
	}

	public function getSuperflatWhereConditionArray(): ?array{
		return $this->getFlatWhereConditionArray();
	}

	public function inferParameterCount():int{
		return $this->getParameterCount();
	}

	public function getConditionalColumnNames(): array{
		return $this->getColumnNames();
	}

	public function __toString(): string{
		return $this->toSQL();
	}
}
