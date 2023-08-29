<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use Exception;
use mysqli;

/**
 * trait for DataStructures with a subjectKey
 *
 * @author j
 *        
 */
trait SubjectiveTrait
{

	use MultipleColumnDefiningTrait;

	/**
	 *
	 * @return UserOwned|NULL
	 */
	public function getSubjectClass()
	{
		$f = __METHOD__; //"SubjectiveTrait(".static::getShortClass().")->getSubjectClass()";
		try {
			if ($this->hasSubjectData()) {
				return $this->getSubjectData()->getClass();
			}
			return $this->getColumn("subjectKey")->getForeignDataStructureClass();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setSubjectDataType(string $type): string
	{
		return $this->setColumnValue('subjectDataType', $type);
	}

	public function getSubjectDataType()
	{
		$f = __METHOD__; //"SubjectiveTrait(".static::getShortClass().")->getSubjectDataType()";
		try {
			// Debug::print("{$f} entered");
			$type = $this->getColumnValue('subjectDataType');
			if (isset($type)) {
				return $type;
			} elseif ($this->hasSubjectData()) {
				$target = $this->getSubjectData();
				$type = $target->getDataType();
				return $this->setSubjectDataType($type);
			}
			Debug::error("{$f} target type is undefined, as is the target object");
			return DATATYPE_UNKNOWN;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getSubjectData(){
		return $this->getForeignDataStructure('subjectKey');
	}

	public function hasSubjectData():bool{
		return $this->hasForeignDataStructure("subjectKey");
	}

	public function setSubjectData(DataStructure $obj): ?DataStructure{
		$f = __METHOD__;
		try {
			$status = $obj->getObjectStatus();
			if ($status === ERROR_NOT_FOUND) {
				Debug::error("{$f} object was deleted");
				return null;
			}
			$ret = $this->setForeignDataStructure('subjectKey', $obj);
			if (! $this->hasSubjectData()) {
				Debug::error("{$f} immediately after setting subject data, it is undefined");
			}
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getSubjectSubtype(){
		return $this->getColumnValue("subjectSubtype");
	}

	public function hasSubjectSubtype():bool{
		return $this->hasColumnValue("subjectSubtype");
	}

	public function setSubjectSubtype(string $subtype): string{
		return $this->setColumnValue("subjectSubtype", $subtype);
	}

	public function hasSubjectKey():bool{
		return $this->hasColumnValue("subjectKey");
	}

	public function setSubjectKey(string $k): string{
		return $this->setColumnValue('subjectKey', $k);
	}

	public function getSubjectKey():string{
		return $this->getColumnValue('subjectKey');
	}

	public function acquireSubjectData(mysqli $mysqli): ?DataStructure
	{
		return $this->acquireForeignDataStructure($mysqli, "subjectKey"); // , false, 3);
	}
}
