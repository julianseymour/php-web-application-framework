<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\common\NewNameTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class AlterTablespaceStatement extends DefineTablespaceStatement
{

	use NewNameTrait;

	protected $activity;

	protected $datafileOperation;

	public function setDatafileOperation($op)
	{
		$f = __METHOD__; //AlterTablespaceStatement::getShortClass()."(".static::getShortClass().")->setDatafileOperation()";
		if ($op == null) {
			unset($this->datafileOperation);
			if ($this->hasDatafilename()) {
				unset($this->datafilename);
			}
			return null;
		} elseif (! is_string($op)) {
			Debug::error("{$f} datafile operation must be a string");
		}
		$op = strtolower($op);
		switch ($op) {
			case DIRECTIVE_ADD:
			case DIRECTIVE_DROP:
				break;
			default:
				Debug::error("{$f} invalid datafile operation \"{$op}\"");
				return null;
		}
		return $this->datafileOperation = $op;
	}

	public function hasDatafileOperation()
	{
		return isset($this->datafileOperation);
	}

	public function getDatafilenOperation()
	{
		$f = __METHOD__; //AlterTablespaceStatement::getShortClass()."(".static::getShortClass().")->getDatafileOperation()";
		if (! $this->hasDatafileOperation()) {
			Debug::error("{$f} datafile operation is undefined");
		}
		return $this->datafileOperation;
	}

	public function addDatafile($name)
	{
		$this->setDatafileOperation(DIRECTIVE_ADD);
		return parent::addDatatile($name);
	}

	public function dropDatafile($name)
	{
		$this->setDatafileOperation(DIRECTIVE_DROP);
		$this->setDatafilename($name);
		return $this;
	}

	public function setActivity($a)
	{
		$f = __METHOD__; //AlterTablespaceStatement::getShortClass()."(".static::getShortClass().")->setActivity()";
		if ($a == null) {
			unset($this->activity);
			return null;
		} elseif (! is_string($a)) {
			Debug::error("{$f} activity must be a string");
		}
		$a = strtolower($a);
		switch ($a) {
			case ACTIVITY_ACTIVE:
			case ACTIVITY_INACTIVE:
				break;
			default:
				Debug::error("{$f} invalid activity \"{$a}\"");
				return $this->setActivity(null);
		}
		return $this->activity = $a;
	}

	public function hasActivity()
	{
		return isset($this->activity);
	}

	public function getActivity()
	{
		$f = __METHOD__; //AlterTablespaceStatement::getShortClass()."(".static::getShortClass().")->getActivity()";
		if (! $this->hasActivity()) {
			Debug::error("{$f} activity is undefined");
		}
		return $this->activity;
	}

	public function set($a)
	{
		$this->setActivity($a);
		return $this;
	}

	public function getQueryStatementString()
	{
		// ALTER
		$string = "alter ";
		// [UNDO]
		if ($this->getUndoFlag()) {
			$string .= "undo ";
		}
		// TABLESPACE tablespace_name
		$string .= "tablespace " . $this->getTablespaceName();
		$engine = $this->getStorageEngine();
		if ($engine === STORAGE_ENGINE_INNODB || $engine === STORAGE_ENGINE_NDB) {
			if ($engine === STORAGE_ENGINE_NDB) { // NDB only:
			                                      // {ADD | DROP} DATAFILE 'file_name'
				if ($this->hasDatafilename()) {
					$dfn = escape_quotes($this->getDatafilename(), QUOTE_STYLE_SINGLE);
					$string .= " " . $this->getDatafilenOperation() . " datafile '{$dfn}'";
				}
				// [INITIAL_SIZE [=] size]
				if ($this->hasInitialSize()) {
					$string .= " initial_size " . $this->getInitialSize();
				}
				// [WAIT]
				if ($this->getWaitFlag()) {
					$string .= " wait";
				}
			}
			// InnoDB and NDB: //[RENAME TO tablespace_name]
			if ($this->hasNewName()) {
				$string .= " rename to " . $this->getNewName();
			}
			if ($engine === STORAGE_ENGINE_INNODB) { // InnoDB only:
			                                         // [AUTOEXTEND_SIZE [=] 'value']
				if ($this->hasAutoextendSize()) {
					$value = escape_quotes($this->getAutoextendSize(), QUOTE_STYLE_SINGLE);
					$string .= " autoextend_size '{$value}'";
				}
				// [SET {ACTIVE | INACTIVE}]
				if ($this->hasActivity()) {
					$string .= " set " . $this->getActivity();
				}
				// [ENCRYPTION [=] {'Y' | 'N'}]
				if ($this->hasEncryption()) {
					$encryption = escape_quotes($this->getEncryption(), QUOTE_STYLE_SINGLE);
					$string .= " encryption '{$encryption}'";
				}
			}
			// InnoDB and NDB: //[ENGINE [=] engine_name]
			$string .= " engine {$engine}";
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->activity);
		unset($this->datafileOperation);
		unset($this->newName);
	}
}
