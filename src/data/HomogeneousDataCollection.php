<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\load\LoadDataStatement;
use Exception;
use mysqli;

class HomogeneousDataCollection extends DataCollection{

	use DataStructureClassTrait;

	public function __construct($class = null){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered with data structure class \"{$class}\"; about to call parent function");
		}
		parent::__construct();
		if($print){
			Debug::print("{$f} returned from parent function");
		}
		if($class != null){
			if($print){
				Debug::print("{$f} setting data structure class to \"{$class}\"");
			}
			$this->setDataStructureClass($class);
		}else{
			Debug::error("{$f} data structure class parameter was not provided");
		}
	}

	public function importCSV(mysqli $mysqli, string $filename, $destroy = false){
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$dsc = $this->getDataStructureClass();
			if(class_exists(LoadDataStatement::class)){
				if(!method_exists($dsc, 'getTableNameStatic')){
					Debug::error("{$f} table name cannot be determined statically for class \"{$dsc}\"");
				}
				$db = $dsc::getDatabaseNameStatic();
				$table = $dsc::getTableNameStatic();
				if($print){
					Debug::print("{$f} database \"{$db}\", table \"{$table}\"");
				}
				$load_data = QueryBuilder::loadData()->local()->infile($filename)->columnsTerminatedby(',')->linesTerminatedBy("\\n")->ignoreRows(1)->intoTable($db, $table);
				$status = $load_data->executeGetStatus($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} load data statement returned error status \"{$err}\"");
				}elseif($print){
					Debug::print("{$f} successfully executed load data statement");
				}
			}else{
				$file = fopen($filename, "r");
				$columns = explode(',', rtrim(fgets($file)));
				// $inserted = 0;
				while(!feof($file)){
					$line = rtrim(fgets($file));
					if(empty($line)){
						if($print){
							Debug::print("{$f} trimmed empty line");
						}
						break;
					}elseif($print){
						Debug::print("{$f} trimmed line \"{$line}\"");
					}
					$struct = new $dsc();
					$struct->setFlag("disableLog", true);
					$struct->setOnDuplicateKeyUpdateFlag(true);
					$values = explode(",", $line);
					foreach($columns as $num => $column_name){
						if(!array_key_exists($num, $values)){
							Debug::warning("{$f} invalid array index \"{$num}\"");
							Debug::printArray($values);
							Debug::printStackTrace();
						}
						$value = $values[$num];
						if(starts_with($value, '"') && ends_with($value, '"')){
							$value = substr($value, 1, strlen($value) - 1);
						}
						$column = $struct->getColumn($column_name);
						$column->setValue($column->cast($value));
					}
					if($destroy && $mysqli != null){
						$status = $struct->insert($mysqli);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							$id = $struct->getIdentifierValue();
							Debug::error("{$f} inserting data structure with ID \"{$id}\" returned error status \"{$err}\"");
						}
						// $inserted++;
						// Debug::checkMemoryUsage("Inserted {$inserted} {$dsc}"); //Debug::print("{$f} inserted {$inserted} {$dsc}");
						if($struct->isRegistrable()){
							registry()->deregister($struct->getIdentifierValue());
						}
						deallocate($struct);
					}else{
						$this->collect($struct);
					}
				}
				fclose($file);
				if($destroy){
					unlink($filename);
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->dataStructureClass, $deallocate);
	}
}
