<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\routine\CreateRoutineStatement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

class CreateStoredRoutinesUseCase extends UseCase{
	
	protected $databaseConnection;
	
	protected $routineCount;
	
	public function setDatabaseConnection(?mysqli $mysqli):?mysqli{
		return $this->databaseConnection = $mysqli;
	}
	
	public function hasDatabaseConnection():bool{
		return isset($this->databaseConnection);
	}
	
	public function getDatabaseConnection():mysqli{
		$f = __METHOD__;
		if(!$this->hasDatabaseConnection()){
			return $this->setDatabaseConnection(db()->reconnect(AdminWriteCredentials::class));
			Debug::error("{$f} database connection is undefined");
		}
		return $this->databaseConection;
	}
	
	public function setRoutineCount(?int $count):?int{
		return $this->routineCount = $count;
	}
	
	public function getRoutineCount():int{
		$f = __METHOD__;
		if(!$this->hasRoutineCount()){
			Debug::error("{$f} routine count is undefined");
		}
		return $this->routineCount;
	}
	
	public function hasRoutineCount():bool{
		return isset($this->routineCount);
	}
	
	public function execute():int{
		if(directive() === DIRECTIVE_SUBMIT){
			$count = $this->setRoutineCount($this->createStoredRoutines($this->getDatabaseConnection()));
			if($count < 0){
				return $this->getObjectStatus();
			}
		}
		return SUCCESS;
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}
	
	public function createStoredRoutines(mysqli $mysqli):int{
		$f = __METHOD__;
		try{
			$print = false;
			$routines = mods()->getStoredRoutines();
			if(empty($routines)){
				if($print){
					Debug::print("there are no stored routines to create");
				}
				return 0;
			}
			$count = 0;
			foreach($routines as $routine){
				$name = back_quote($routine->getDatabaseNameStatic()).".".back_quote($routine->getName());
				$type = $routine->getRoutineType();
				if($print){
					Debug::print("{$type} {$name}");
				}
				switch($type){
					case ROUTINE_TYPE_FUNCTION:
						$drop = QueryBuilder::dropFunctionIfExists($name);
						break;
					case ROUTINE_TYPE_PROCEDURE:
						$drop = QueryBuilder::dropProcedureIfExists($name);
						break;
					default:
						Debug::error("invalid routine type \"{$type}\"");
				}
				if($print){
					$qs = $drop->toSQL();
					Debug::print("about to execute query \"{$qs}\"");
				}
				$status = $drop->executeGetStatus($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("dropping routine \"{$name}\" returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return -1;
				}elseif($print){
					Debug::print("successfully executed drop statement for routine \"{$name}\"");
				}
				$create = new CreateRoutineStatement($routine);
				$create->setDeterministicFlag(true);
				//$create->setDatabase("data");
				if($print){
					$qs = $create->toSQL();
					Debug::print("about to execute query \"{$qs}\"");
				}
				$status = $create->executeGetStatus($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("creating routine \"{$name}\" returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return -1;
				}elseif($print){
					Debug::print("successfully executed create statement for routine \"{$name}\"");
				}
				$count++;
			}
			if($print){
				Debug::print("returning successfully");
			}
			return $count;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getPageContent():?array{
		return [new CreateStoredRoutinesForm(ALLOCATION_MODE_LAZY)];
	}
	
	public function getActionAttribute():?string{
		return "/create_routines";
	}
}
