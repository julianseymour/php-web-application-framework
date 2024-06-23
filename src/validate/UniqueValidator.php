<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructureClassTrait;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class UniqueValidator extends Validator implements TypeSpecificInterface{

	use DataStructureClassTrait;
	use MultipleColumnNamesTrait;
	use ParametricTrait;
	use TypeSpecificTrait;

	public function __construct($data_class, $typedef, ...$columnNames){
		parent::__construct();
		$this->setDataStructureClass($data_class);
		$this->setTypeSpecifier($typedef);
		if(isset($columnNames)){
			$this->setColumnNames($columnNames);
		}
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return array_merge(parent::declarePropertyTypes(), [
			"columnNames" => 's'
		]);
	}

	public function evaluate(&$validate_me): int{
		$f = __METHOD__;
		try{
			$print = false;
			$typedef = $this->getTypeSpecifier();
			$columnNames = $this->getColumnNames();
			$select = $this->getDataStructureClass()::selectStatic(null, ...$columnNames);
			$count = count($columnNames);
			switch($count){
				case 0:
					if($print){
						Debug::print("{$f} 0 select variables");
					}
					$where = null;
					break;
				case 1:
					if($print){
						Debug::print("{$f} 1 select variable");
					}
					$where = new WhereCondition($columnNames[0], OPERATOR_EQUALS);
					break;
				default:
					if($print){
						Debug::print("{$f} {$count} select variables");
					}
					$where = new AndCommand();
					foreach($columnNames as $columnName){
						$where->pushParameters(new WhereCondition($columnName, OPERATOR_EQUALS));
					}
					break;
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			if($where == null){
				if($print){
					Debug::print("{$f} where condition is null");
				}
				$count = $select->executeGetResultCount($mysqli);
			}else{
				if($print){
					Debug::print("{$f} where condition is not null");
				}
				$select->where($where);
				$values = $this->getParameters();
				$count = $select->prepareBindExecuteGetResultCount($mysqli, $typedef, ...$values);
			}
			deallocate($select);
			if($count === 0){
				if($print){
					Debug::print("{$f} success, 0 results");
				}
				return SUCCESS;
			}elseif($print){
				Debug::warning("{$f} count is {$count}");
			}
			return $this->getSpecialFailureStatus();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->dataStructureClass, $deallocate);
		$this->release($this->typeSpecifier, $deallocate);
	}

	public function extractParameters(&$in):array{
		$out = [];
		foreach($this->getColumnNames() as $columnName){
			$out[$columnName] = $in[$columnName];
		}
		return $out;
	}
}
