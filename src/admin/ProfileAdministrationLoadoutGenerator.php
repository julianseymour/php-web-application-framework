<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\use_case;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ProfileAdministrationLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(?PlayableUser $user = null, ?UseCase $use_case = null): ?array{
		$class = $use_case->getCorrespondentClass();
		return [
			'correspondent' => [
				$class => $class::selectStatic()->where(
					new WhereCondition($class::getIdentifierNameStatic(), OPERATOR_EQUALS)
				)->withParameters([
					getInputParameter("correspondentKey", $use_case)
				])->withTypeSpecifier('s')
			]
		];
	}

	public function getNonRootNodeTreeSelectStatements(DataStructure $object, ?UseCase $use_case = null): ?array
	{
		$f = __METHOD__; //ProfileAdministrationLoadoutGenerator::getShortClass()."(".static::getShortClass().")->getNonRootNodeTreeSelectStatements()";
		$print = false;
		$type = $object->getDataType();
		switch ($type) {
			case DATATYPE_USER:
				if ($object->getAccountType() !== $use_case->getCorrespondentClass()::getAccountTypeStatic()) {
					if ($print) {
						Debug::print("{$f} object is not the correct profile type");
					}
					return null;
				}
				$doc = $use_case->getDataOperandClass();
				$dummy = new $doc();
				$select = $dummy->select();
				if ($print) {
					$qstring = $select->toSQL();
					Debug::print("{$f} generated query \"{$qstring}\"");
				}
				if ($dummy->hasColumn("userKey")) {
					$params = [
						getInputParameter("correspondentKey", use_case())
					];
					$pm = $dummy->getColumn("userKey")->getPersistenceMode();
					switch ($pm) {
						case PERSISTENCE_MODE_DATABASE:
							$where = new WhereCondition("userKey", OPERATOR_EQUALS);
							break;
						case PERSISTENCE_MODE_INTERSECTION:
							$where = $doc::whereIntersectionalHostKey(mods()->getUserClass($object->getUserAccountType()), "userKey");
							array_push($params, "userKey");
							break;
						default:
							Debug::error("{$f} unsupported persistence mode \"{$pm}\"");
					}
					$select = $select->where($where);
					if ($params) {
						$select->withParameters($params);
					}
				}
				return [
					$doc::getPhylumName() => [
						$doc => $select
					]
				];
			default:
				return null;
		}
	}
}

