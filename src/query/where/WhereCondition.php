<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\where;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ParameterCountingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;
use Exception;

class WhereCondition extends ExpressionCommand 
implements 
SelectStatementInterface, 
StringifiableInterface, 
TypeSpecificInterface, 
WhereConditionalInterface
{

	use ColumnNameTrait;
	use ParameterCountingTrait;
	use SelectStatementTrait;
	use TypeSpecificTrait;

	public function __construct($varname=null, string $operator = OPERATOR_EQUALS, ?string $typeSpecifier = null, ?SelectStatement $selectStatement = null){
		parent::__construct();
		if($varname !== null){
			$this->setColumnName($varname);
		}
		if($operator !== null){
			$this->setOperator($operator);
		}
		if($typeSpecifier !== null){
			$this->setTypeSpecifier($typeSpecifier);
		}
		if($selectStatement !== null){
			$this->setSelectStatement($selectStatement);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasColumnName()){
			$this->setColumnName(replicate($that->getColumnName()));
		}
		if($that->hasParameterCount()){
			$this->setParameterCount(replicate($that->getParameterCount()));
		}
		if($that->hasTypeSpecifier()){
			$this->setTypeSPecifier(replicate($that->getTypeSpecifier()));
		}
		if($that->hasSelectStatement()){
			$this->setSelectStatement(replicate($that->getSelectStatement()));
		}
		return $ret;
	}
	
	public function hasUnbindableOperator(){
		return false !== array_search($this->getOperator(), [
			OPERATOR_IS_NULL,
			OPERATOR_IS_NOT_NULL
		]);
	}

	public function getTypeSpecifier(){
		$f = __METHOD__;
		if(!$this->hasTypeSpecifier()){
			$cn = $this->getColumnName();
			if($cn instanceof TypeSpecificInterface){
				return $cn->getTypeSpecifier();
			}
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} no type specifier for column \"{$cn}\". Instantiated {$decl}");
		}
		return $this->typeSpecifier;
	}

	public static function in($varname, ?int $parameterCount = null){
		if($parameterCount === 1){
			return new WhereCondition($varname, OPERATOR_EQUALS);
		}
		$where = new WhereCondition($varname, OPERATOR_IN);
		if($parameterCount !== null){
			$where->setParameterCount($parameterCount);
		}
		return $where;
	}

	public function getFlatWhereConditionArray(): ?array{
		if($this->hasSelectStatement()){
			return $this->getSelectStatement()->getFlatWhereConditionArray();
		}
		return [
			$this
		];
	}

	public function getSuperflatWhereConditionArray():?array{
		if($this->hasSelectStatement()){
			return $this->getSelectStatement()->getSuperflatWhereConditionArray();
		}
		return [
			$this
		];
	}

	public function inferParameterCount():int{
		$f = __METHOD__;
		if($this->hasParameterCount()){
			return $this->getParameterCount();
		}
		$operator = $this->getOperator();
		switch($operator){
			case OPERATOR_IS_NULL:
			case OPERATOR_IS_NOT_NULL:
				return 0;
			case OPERATOR_IN:
			case OPERATOR_NOT_IN:
				if(!$this->hasSelectStatement()){
					Debug::error("{$f} WhereCondition must have a SelectStatement to infer parameter count");
				}
				return $this->getSelectStatement()->inferParameterCount();
			default:
				return 1;
		}
	}

	public function getParameterCount(): int{
		if($this->hasParameterCount()){
			return $this->parameterCount;
		}
		return $this->inferParameterCount();
	}

	public function getRequiredParameterCount():int{
		return $this->inferParameterCount();
	}

	public function toSQL():string{
		$f = __METHOD__;
		try{
			$print = false;
			$select = null;
			if($this->hasSelectStatement()){
				$select = $this->getSelectStatement();
				if($select instanceof SQLInterface){
					$select = "(".$select->toSQL().")";
				}
			}
			$operator = $this->getOperator();
			switch($operator){
				case OPERATOR_IN:
				case OPERATOR_NOT_IN:
					$qmark = "(";
					if($select !== null){
						if($print){
							Debug::print("{$f} select statement is not null");
						}
						$qmark .= $select;
					}else{
						if($print){
							Debug::print("{$f} select statement is null");
						}
						for ($i = 0; $i < $this->getParameterCount(); $i ++){
							if($i > 0){
								$qmark .= ",";
							}
							$qmark .= "?";
						}
					}
					$qmark .= ")";
					break;
				case OPERATOR_IS_NULL:
				case OPERATOR_IS_NOT_NULL:
					$qmark = ""; // null";
					break;
				case OPERATOR_STARTS_WITH:
					$operator = "like";
					if($select !== null){
						$qmark = $select;
					}else{
						$qmark = "?";
					}
					$qmark = "CONCAT('%',{$qmark})";
					break;
				case OPERATOR_CONTAINS:
					$operator = "like";
					if($select !== null){
						$qmark = $select;
					}else{
						$qmark = "?";
					}
					$qmark = "CONCAT('%',{$qmark},'%')";
					break;
				case OPERATOR_ENDS_WITH:
					$operator = "like";
					if($select !== null){
						$qmark = $select;
					}else{
						$qmark = "?";
					}
					$qmark = "CONCAT({$qmark},'%')";
					break;
				default:
					if($select !== null){
						$qmark = $select;
					}else{
						$qmark = "?";
					}
					break;
			}
			$var = $this->getColumnName();
			if($var instanceof SQLInterface){
				$var = $var->toSQL();
			}
			$string = "{$var} {$operator} {$qmark}";
			if($this->hasEscapeType() && $this->getEscapeType() === ESCAPE_TYPE_PARENTHESIS){
				$string = "({$string})";
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getCommandId(): string{
		return "where";
	}

	public function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->parameterCount, $deallocate);
		if($this->hasSelectStatement()){
			$this->release($this->selectStatement, $deallocate);
		}
		$this->release($this->typeSpecifier, $deallocate);
	}

	public function getConditionalColumnNames(): array{
		if($this->inferParameterCount() === 0){
			return [];
		}
		return [
			$this->getColumnName()
		];
	}

	public function __toString(): string{
		return $this->toSQL();
	}
	
	/**
	 * generates a WhereCondition for locating something in an intersections table that references an object of this class
	 *
	 * @param string|DataStructure $hostClass : host class used to generate IntersectionData
	 * @param string|DataStructure $foreignClass : foreign class used to generate IntersectionData
	 * @param string $relationship : name of foreign key -- needed because of embedded columns
	 * @param string $operator : operator used to build WhereConditions
	 * @return WhereCondition
	 */
	public static function intersectional(string $hostClass, string $foreignClass, string $select_expr, string $relationship, string $operator = OPERATOR_EQUALS){
		$f = __METHOD__;
		$print = false;
		if(is_object($hostClass)){
			$hostClass = get_class($hostClass);
		}
		if(is_object($foreignClass)){
			$foreignClass = get_class($foreignClass);
		}
		$idn = $hostClass::getIdentifierNameStatic();
		if($print){
			Debug::print("{$f} about to create new IntersectionData({$hostClass}, {$foreignClass}, {$relationship})");
		}
		$intersection = new IntersectionData($hostClass, $foreignClass, $relationship);
		switch($select_expr){
			case "hostKey":
				$column_name = "foreignKey";
				break;
			case "foreignKey":
				$column_name = "hostKey";
				break;
			default:
				Debug::error("{$f} invalid columns name \"{$column_name}\"");
		}
		$select = new SelectStatement($select_expr);
		$select->from(
			$intersection->getDatabaseName(),
			$intersection->getTableName()
		)->where(
			new AndCommand(
				new WhereCondition($column_name, $operator, 's'),
				new WhereCondition("relationship", $operator, 's')
			)
		);
		$ret = new WhereCondition(
			$idn,
			OPERATOR_IN,
			$hostClass::getTypeSpecifierStatic($idn),
			$select
		);
		deallocate($intersection);
		if($print){
			$ret->setParameterCount(1);
			Debug::print("{$f} returning \"{$ret}\"");
			$ret->setParameterCount(null);
		}
		return $ret;
	}
}
