<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\view;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AlgorithmOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\SQLSecurityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;

abstract class ViewStatement extends QueryStatement implements SelectStatementInterface{

	use AlgorithmOptionTrait;
	use DatabaseNameTrait;
	use MultipleColumnNamesTrait;
	use NamedTrait;
	use SelectStatementTrait;
	use SQLSecurityTrait;

	protected $checkOption;

	public function __construct($db = null, $name = null, $selectStatement = null){
		parent::__construct();
		$this->requirePropertyType('columnNames', 's');
		if($db != null){
			$this->setDatabaseName($db);
		}
		if($name !== null){
			$this->setName($name);
		}
		if($selectStatement !== null){
			$this->setSelectStatement($selectStatement);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->algorithmType, $deallocate);
		$this->release($this->checkOption, $deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->selectStatement, $deallocate);
		$this->release($this->sqlSecurityType, $deallocate);
		$this->release($this->userDefiner, $deallocate);
	}
	
	public function setAlgorithm($alg){
		$f = __METHOD__;
		if(!is_string($alg)){
			Debug::error("{$f} algortihm must be a string");
		}
		$alg = strtolower($alg);
		switch($alg){
			case ALGORITHM_UNDEFINED:
			case ALGORITHM_MERGE:
			case ALGORITHM_TEMPTABLE:
				break;
			default:
				Debug::error("{$f} invalid algorithm \"{$alg}\"");
		}
		if($this->hasAlgorithm()){
			$this->release($this->algorithmType);
		}
		return $this->algorithmType = $this->claim($alg);
	}

	public function setCheckOption($o){
		$f = __METHOD__;
		if(!is_string($o)){
			Debug::error("{$f} check option must be a string");
		}
		$o = strtolower($o);
		switch($o){
			case CHECK_OPTION_CHECK:
			case CHECK_OPTION_CASCADED:
			case CHECK_OPTION_LOCAL:
				break;
			default:
				Debug::error("{$f} invalid check option \"{$o}\"");
		}
		if($this->hasCheckOption()){
			$this->release($this->checkOption);
		}
		return $this->checkOption = $this->claim($o);
	}

	public function hasCheckOption():bool{
		return isset($this->checkOption);
	}

	public function getCheckOption(){
		$f = __METHOD__;
		if(!$this->hasCheckOption()){
			Debug::error("{$f} check option is undefined");
		}
		return $this->checkOption;
	}

	public function withCheckOption($o):ViewStatement{
		$this->setCheckOption($o);
		return $this;
	}

	public function as($s):ViewStatement{
		$this->setSelectStatement($s);
		return $this;
	}

	public function view($name):ViewStatement{
		$this->setName($name);
		return $this;
	}
}
