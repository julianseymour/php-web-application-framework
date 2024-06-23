<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\EscapeTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
use Exception;
use mysqli;
use mysqli_result;
use mysqli_sql_exception;
use mysqli_stmt;

abstract class QueryStatement extends Basic 
implements SQLInterface, StringifiableInterface, TypeSpecificInterface{

	use DatabaseVersionTrait;
	use EscapeTypeTrait;
	use ParametricTrait;
	use TypeSpecificTrait;

	protected $fallbackStatement;

	public abstract function getQueryStatementString();

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->fallbackStatement, $deallocate);
		$this->release($this->escapeType, $deallocate);
		$this->release($this->propertyTypes, $deallocate);
		$this->release($this->requiredMySQLVersion, $deallocate);
		$this->release($this->typeSpecifier, $deallocate);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		//use DatabaseVersionTrait;
		if($that->hasRequiredMySQLVersion()){
			$this->setRequiredMySQLVersion(replicate($that->getRequiredMySQLVersion()));
		}
		//use EscapeTypeTrait;
		if($that->hasEscapeType()){
			$this->setEscapeType(replicate($that->getEscapeType()));
		}
		//use TypeSpecificTrait;
		if($that->hasTypeSpecifier()){
			$this->setTypeSpecifier(replicate($that->getTypeSpecifier()));
		}
		//protected $fallbackStatement;
		if($that->hasFallbackStatement()){
			$this->setFallbackStatement(replicate($that->getFallbackStatement()));
		}
		$this->copyProperties($that);
		return $ret;
	}
	
	public final function __toString(): string{
		return $this->toSQL();
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param string $typedef
	 * @param mixed[] $params
	 * @return int
	 */
	public function prepareBindExecuteGetStatus(mysqli $mysqli, $typedef, ...$params): int{
		$f = __METHOD__;
		$print = false;
		if(!is_string($typedef)){
			Debug::error("{$f} type specifier must be a string");
		}
		$st = $this->prepareBindExecuteGetStatement($mysqli, $typedef, ...$params);
		if(!isset($st)){
			if($print){
				Debug::print("{$f} statement returned null");
			}
			return $this->getObjectStatus();
		}
		return SUCCESS;
	}

	public function prepareBindExecuteGetStatement(mysqli $mysqli, string $typedef, ...$params): ?mysqli_stmt{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			// error checking
			if(!is_string($typedef)){
				Debug::error("{$f} type specifier must be a string");
			}elseif(!isset($mysqli)){
				Debug::error("{$f} mysql connection is null");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}elseif($mysqli->connect_errno){
				Debug::warning("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}){$mysqli->connect_error}");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}elseif(!$mysqli->ping()){
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}elseif($print){
				$pcount = count($params);
				Debug::print("{$f} entered with type definition string \"{$typedef}\" and {$pcount} paramaters");
			}
			// generate query string
			$qstring = $this->toSQL();
			if($typedef == null || strlen($typedef) == 0){
				$qcount = substr_count($qstring, '?');
				if($qcount === 0){
					if($print){
						Debug::print("{$f} question mark count is null");
					}
				}else{
					Debug::error("{$f} type definition string is undefined");
				}
			} else
				foreach($params as $param){
					if(is_array($param)){
						Debug::error("{$f} you forgot to unroll the parameters");
					}
				}
			// prepare query statement
			if($print){
				Debug::print("{$f} about to prepare query statement \"{$qstring}\"");
			}
			//Debug::checkMemoryUsage();
			if($st = $mysqli->prepare($qstring)){
				if($print){
					Debug::print("{$f} successfully prepared query \"{$qstring}\"");
					// Debug::print("{$f} about to bind the following parameters");
					// Debug::printArray($params);
				}
			}else{
				Debug::warning("{$f} failed to prepare query \"{$qstring}\": \"{$mysqli->error}\"");
				$this->setObjectStatus(ERROR_MYSQL_PREPARE);
				return db()->rollbackTransaction($mysqli);
			}
			// bind parameters
			$count = count($params);
			$length = strlen($typedef);
			if($count !== $length){
				$decl = $this->getDeclarationLine();
				Debug::warning("{$f} parameter count {$count} does not match length of type definition string \"{$typedef}\" ({$length}) for query \"{$qstring}\". Declared {$decl}. About to print parameters");
				Debug::printArray($params);
				Debug::printStackTrace();
			}elseif($print){
				Debug::print("{$f} parameter count {$count} and length of type definition string \"{$typedef}\" ({$length}) match for query \"{$qstring}\" with the following parameters:");
				Debug::printArray($params);
			}
			$bound = $st->bind_param($typedef, ...$params);
			if(!$bound){
				$decl = $this->getDeclarationLine();
				Debug::warning("{$f} parameter binding failed for query \"{$qstring}\", instantiated {$decl} with type definition string \"{$typedef}\" and the following parameters:");
				Debug::printArray($params);
				$this->setObjectStatus(ERROR_MYSQL_BIND);
				Debug::printStackTrace();
				return db()->rollbackTransaction($mysqli);
			}elseif($print){
				Debug::print("{$f} successfully bound parameters");
			}
			// execute prepared query statement
			db()->access();
			if($st->execute()){
				if($print){
					Debug::print("{$f} successfully executed prepared query statement");
				}
				return $st;
			}
			Debug::warning("{$f} query \"{$qstring}\" failed: \"{$st->error}\"");
			$this->setObjectStatus(ERROR_MYSQL_EXECUTE);
			return db()->rollbackTransaction($mysqli);
		}catch(mysqli_sql_exception $x){
			try{
				$s = $this->toSQL();
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} fatal exception executing query \"{$s}\", instantiated on {$decl}, with type specifier \"{$typedef}\" and the following parameters:");
				Debug::printArray($params);
			}catch(Exception $y){
				Debug::warning("{$f} unable to generate string");
			}
			x($f, $x);
		}
	}

	public function executeGetStatus(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		if($this->hasTypeSpecifier()){
			return $this->prepareBindExecuteGetStatus($mysqli, $this->getTypeSpecifier(), ...$this->getParameters());
		}
		$result = $this->executeGetResult($mysqli);
		if(!isset($result)){
			if($print){
				Debug::print("{$f} no result");
			}
			return $this->getObjectStatus();
		}
		return SUCCESS;
	}

	/**
	 * Calls prepareBindExecuteGetResult on queries with predefined type definition strings and parameter lists.
	 * Also useful for statements that have no variables to bind whatsoever, but return a result
	 *
	 * @param mysqli $mysqli
	 * @param string $query
	 * @return mysqli_result
	 */
	public function executeGetResult(mysqli $mysqli){
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if(!isset($mysqli)){
				Debug::warning("{$f} mysql object is undefined");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}
			$qstring = $this->toSQL();
			if($print){
				Debug::print("{$f} entered; about to query \"{$qstring}\"");
			}
			if($this instanceof WhereConditionalStatement){
				$count = $this->inferParameterCount();
				if($count > 0){
					$decl = $this->getDeclarationLine();
					Debug::error("{$f} No parameters for query \"{$qstring}\", but required parameter count is {$count}. You may have forgotten to provide the type specifier. Query statement was declared {$decl}");
				}
			}elseif($print){
				Debug::print("{$f} this is not a where conditional statement");
			}
			db()->access();
			$result = $mysqli->query($qstring);
			if($result){
				if($print){
					Debug::print("{$f} successfully queried \"{$qstring}\"");
				}
				return $result;
			}
			Debug::warning("{$f} error querying \"{$qstring}\": \"{$mysqli->error}\"");
			$this->setObjectStatus(ERROR_MYSQL_QUERY);
			return db()->rollbackTransaction($mysqli);
		}catch(mysqli_sql_exception $x){
			try{
				$qstring = $this->toSQL();
				$decl = $this->getDeclarationLine();
				Debug::warning("{$f} fatal exception executing query statement \"{$qstring}\". Instantiated {$decl}");
			}catch(Exception $y){
				Debug::warning("{$f} could not convert query statement to string");
			}
			x($f, $x);
		}
	}

	public function executeGetResultCount(mysqli $mysqli): int{
		$result = $this->executeGetResult($mysqli);
		return $result->num_rows;
	}

	public function setFallbackStatement($obj){
		$f = __METHOD__;
		if(!$obj instanceof QueryStatement){
			Debug::error("{$f} fallback statement must be an instanceof QueryStatement");
		}elseif($this->hasFallbackStatement()){
			$this->release($this->fallbackStatement);
		}
		return $this->fallbackStatement = $this->claim($obj);
	}

	public function hasFallbackStatement():bool{
		return isset($this->fallbackStatement) && $this->fallbackStatement instanceof QueryStatement;
	}

	public function getFallbackStatement(){
		$f = __METHOD__;
		if(!$this->hasFallbackStatement()){
			Debug::error("{$f} fallback statement is undefined");
		}
		return $this->fallbackStatement;
	}

	public function withFallbackStatement($obj){
		$this->setFallbackStatement($obj);
		return $this;
	}

	public final function toSQL(): string{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasRequiredMySQLVersion()){
				$v = $this->getRequiredMySQLVersion();
				if(! hasMinimumMySQLVersion($v)){
					if($print){
						$current = getCurrentMySQLVersion();
						Debug::warning("{$f} insufficient MySQL version ({$current}, must be {$v})");
					}
					if(!$this->hasFallbackStatement()){
						Debug::error("{$f} fallback statement is undefined");
					}
					return $this->getFallbackStatement()->toSQL();
				}
			}elseif($print){
				Debug::print("{$f} required MySQL version is undefined");
			}
			$string = $this->getQueryStatementString();
			if($this->hasEscapeType()){
				$type = $this->getEscapeType();
				switch($type){
					case ESCAPE_TYPE_PARENTHESIS:
						return "({$string})";
					default:
						Debug::error("{$f} invalid escape type \"{$type}\"");
				}
			}
			return $string;
		}catch(mysqli_sql_exception $x){
			x($f, $x);
		}
	}
}
