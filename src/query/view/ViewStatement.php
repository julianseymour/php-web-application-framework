<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\view;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AlgorithmOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\SQLSecurityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;

abstract class ViewStatement extends QueryStatement implements SelectStatementInterface
{

	use AlgorithmOptionTrait;
	use DatabaseNameTrait;
	use MultipleColumnNamesTrait;
	use NamedTrait;
	use SelectStatementTrait;
	use SQLSecurityTrait;

	protected $checkOption;

	public function __construct($db = null, $name = null, $selectStatement = null)
	{
		parent::__construct();
		$this->requirePropertyType('columnNames', 's');
		if($db != null) {
			$this->setDatabaseName($db);
		}
		if($name !== null) {
			$this->setName($name);
		}
		if($selectStatement !== null) {
			$this->setSelectStatement($selectStatement);
		}
	}

	public function setAlgorithm($alg)
	{
		$f = __METHOD__; //ViewStatement::getShortClass()."(".static::getShortClass().")->setAlgorithm()";
		if($alg == null) {
			unset($this->algorithmType);
			return null;
		}elseif(!is_string($alg)) {
			Debug::error("{$f} algortihm must be a string");
		}
		$alg = strtolower($alg);
		switch ($alg) {
			case ALGORITHM_UNDEFINED:
			case ALGORITHM_MERGE:
			case ALGORITHM_TEMPTABLE:
				break;
			default:
				Debug::error("{$f} invalid algorithm \"{$alg}\"");
		}
		return $this->algorithmType = $alg;
	}

	public function setCheckOption($o)
	{
		$f = __METHOD__; //ViewStatement::getShortClass()."(".static::getShortClass().")->setCheckOption()";
		if($o == null) {
			unset($this->checkOption);
			return null;
		}elseif(!is_string($o)) {
			Debug::error("{$f} check option must be a string");
		}
		$o = strtolower($o);
		switch ($o) {
			case CHECK_OPTION_CHECK:
			case CHECK_OPTION_CASCADED:
			case CHECK_OPTION_LOCAL:
				return $this->checkOption = $o;
			default:
				Debug::error("{$f} invalid check option \"{$o}\"");
		}
	}

	public function hasCheckOption()
	{
		return isset($this->checkOption);
	}

	public function getCheckOption()
	{
		$f = __METHOD__; //ViewStatement::getShortClass()."(".static::getShortClass().")->getCheckOption()";
		if(!$this->hasCheckOption()) {
			Debug::error("{$f} check option is undefined");
		}
		return $this->checkOption;
	}

	public function withCheckOption($o)
	{
		$this->setCheckOption($o);
		return $this;
	}

	public function as($s)
	{
		$this->setSelectStatement($s);
		return $this;
	}

	public function view($name)
	{
		$this->setName($name);
		return $this;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->algorithmType);
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->checkOption);
		unset($this->name);
		unset($this->selectStatement);
		unset($this->sqlSecurityType);
		unset($this->userDefiner);
	}
}
