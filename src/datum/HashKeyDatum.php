<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class HashKeyDatum extends Sha1HashDatum
{

	public static function generateKeyStatic(string $datatype, ...$unique)
	{
		$f = __METHOD__; //HashKeyDatum::getShortClass()."(".static::getShortClass().")::generateKeyStatic()";
		$print = false;
		if($print){
			Debug::print("{$f} about to generate key using the following key value pairs:");
			Debug::printArray($unique);
		}
		$imploded = implode('-', $unique);
		return sha1("{$datatype}-{$imploded}");
	}

	public function generate(): int
	{
		$f = __METHOD__; //HashKeyDatum::getShortClass()."(".static::getShortClass().")->generate()";
		try{
			$print = false;
			$ds = $this->getDataStructure();
			$unique = []; // $ds->getFilteredColumns(COLUMN_FILTER_UNIQUE, "!".COLUMN_FILTER_ID);
			$composite = $ds->getCompositeUniqueColumnNames();
			if(empty($composite)){
				$dsc = $ds->getClass();
				Debug::error("{$f} object with hash keys must define composite unique column names; please write the function {$dsc}::getCompositeUniqueColumnNames()");
			}elseif($print){
				Debug::print("{$f} about to generate key using the following keys:");
				Debug::printArray($composite);
			}
			foreach($composite as $keyvalues){
				if(!is_array($keyvalues)){
					Debug::error("{$f} getCompositeUniqueColumnNames should return a multidimensional array");
				}
				foreach($keyvalues as $field){
					$value = $ds->getColumnValue($field);
					if(!$ds->getColumn($field)->isNullable() && ($value === null || $value === "")){
						Debug::error("{$f} value of column \"{$field}\" is null or empty string");
					}
					$unique[$field] = $value;
					if($print){
						Debug::print("{$f} value for semi-unique field \"{$field}\" is {$value}");
					}
				}
			}
			foreach($unique as $i => $u){
				if(is_object($u)){
					Debug::error("{$f} value #{$i} is an object of class " . $u->getClass());
				}
			}
			$this->setValue($this->generateKeyStatic($ds->getDataType(), ...array_values($unique)));
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
