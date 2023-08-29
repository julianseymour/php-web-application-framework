<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum\foreign;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\query\GroupConcatenateFunction;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class TemplateKeyListDatum extends KeyListDatum{
	
	protected $appliedTemplateColumnName;
	
	public function __construct(string $name, ?int $relationship_type=null){
		parent::__construct($name, $relationship_type);
		$this->setTemplateFlag(true);
	}
	
	public final function getPersistenceMode():int{
		return PERSISTENCE_MODE_ALIAS;
	}
	
	public function hasAppliedTemplateColumnName():bool{
		return isset($this->appliedTemplateColumnName);
	}
	
	public function setAppliedTemplateColumnName(?string $name):?string{
		if($name == null){
			unset($this->appliedTemplateColumnName);
			return null;
		}
		return $this->appliedTemplateColumnName = $name;
	}
	
	public function getAppliedTemplateColumnName():string{
		$f = __METHOD__;
		if(!$this->hasAppliedTemplateColumnName()){
			Debug::error("{$f} applied template column name is undefined");
		}
		return $this->appliedTemplateColumnName;
	}
	
	public function getAliasExpression(){
		$f = __METHOD__;
		try{
			if($this->hasAliasExpression()){
				return $this->aliasExpression;
			}
			$ds = $this->getDataStructure();
			$applied_column = $ds->getColumn($this->getAppliedTemplateColumnName());
			$applied_class = $applied_column->getForeignDataStructureClass();
			$table1 = $applied_class::getTableNameStatic();
			$intersection = new IntersectionData(
				$applied_class, 
				$ds->getClass(), 
				$applied_column->getConverseRelationshipKeyName()
			);
			$templateKey = "templateKey";
			$group_concat = new GroupConcatenateFunction();
			$group_concat->delimit('","');
			$alias1 = "{$table1}_alias";
			$group_concat->setExpressions(["{$alias1}.{$templateKey}"]);
			$table2 = $intersection->getTableName();
			$alias2 = "{$table2}_alias";
			return QueryBuilder::select(
				new ConcatenateCommand("[\"", $group_concat, "\"]")
			)->from(
				new TableFactor($applied_class::getDatabaseNameStatic(), $table1, $alias1)
			)->where(
				new WhereCondition(
					"{$alias1}.".$applied_class::getIdentifierNameStatic(),
					OPERATOR_IN,
					's',
					QueryBuilder::select(
						new GetDeclaredVariableCommand("{$alias2}.hostKey")
					)->from(
						new TableFactor($intersection->getDatabaseName(), $table2, $alias2)
					)->where(
						new AndCommand(
							BinaryExpressionCommand::equals(
								new GetDeclaredVariableCommand("{$alias2}.relationship"),
								$intersection->getRelationship()
							),
							BinaryExpressionCommand::equals(
								new GetDeclaredVariableCommand("{$alias2}.foreignKey"),
								new GetDeclaredVariableCommand("t0.".$ds->getIdentifierName())
							)
						)
					)
				)
			);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getConverseRelationshipKeyName():string{
		$f = __METHOD__;
		Debug::error("{$f} disabled because objects to which you apply a template do not have direct converse references");
		return "ERROR";
	}
}
