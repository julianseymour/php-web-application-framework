<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\NewNameTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class AlterTablespaceStatement extends DefineTablespaceStatement{

	use NewNameTrait;

	protected $activity;

	protected $datafileOperation;

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->activity, $deallocate);
		$this->release($this->datafileOperation, $deallocate);
		$this->release($this->newName, $deallocate);
	}
	
	public function setDatafileOperation($op){
		$f = __METHOD__;
		if(!is_string($op)){
			Debug::error("{$f} datafile operation must be a string");
		}
		$op = strtolower($op);
		switch($op){
			case DIRECTIVE_ADD:
			case DIRECTIVE_DROP:
				break;
			default:
				Debug::error("{$f} invalid datafile operation \"{$op}\"");
				return null;
		}
		if($this->hasDatafileOperation()){
			$this->release($this->datafileOperation);
		}
		if($this->hasDatafilename()){
			$this->release($this->datafilename);
		}
		return $this->datafileOperation = $this->claim($op);
	}

	public function hasDatafileOperation():bool{
		return isset($this->datafileOperation);
	}

	public function getDatafilenOperation(){
		$f = __METHOD__;
		if(!$this->hasDatafileOperation()){
			Debug::error("{$f} datafile operation is undefined");
		}
		return $this->datafileOperation;
	}

	public function addDatafile($name){
		$this->setDatafileOperation(DIRECTIVE_ADD);
		return parent::addDatatile($name);
	}

	public function dropDatafile($name):AlterTablespaceStatement{
		$this->setDatafileOperation(DIRECTIVE_DROP);
		$this->setDatafilename($name);
		return $this;
	}

	public function setActivity($a){
		$f = __METHOD__;
		if(!is_string($a)){
			Debug::error("{$f} activity must be a string");
		}
		$a = strtolower($a);
		switch($a){
			case ACTIVITY_ACTIVE:
			case ACTIVITY_INACTIVE:
				break;
			default:
				Debug::error("{$f} invalid activity \"{$a}\"");
				return $this->setActivity(null);
		}
		if($this->hasActivity()){
			$this->release($this->activity);
		}
		return $this->activity = $this->claim($a);
	}

	public function hasActivity():bool{
		return isset($this->activity);
	}

	public function getActivity(){
		$f = __METHOD__;
		if(!$this->hasActivity()){
			Debug::error("{$f} activity is undefined");
		}
		return $this->activity;
	}

	public function set($a):AlterTablespaceStatement{
		$this->setActivity($a);
		return $this;
	}

	public function getQueryStatementString():string{
		// ALTER
		$string = "alter ";
		// [UNDO]
		if($this->getUndoFlag()){
			$string .= "undo ";
		}
		// TABLESPACE tablespace_name
		$string .= "tablespace " . $this->getTablespaceName();
		$engine = $this->getStorageEngine();
		if($engine === STORAGE_ENGINE_INNODB || $engine === STORAGE_ENGINE_NDB){
			if($engine === STORAGE_ENGINE_NDB){ // NDB only:
			                                      // {ADD | DROP} DATAFILE 'file_name'
				if($this->hasDatafilename()){
					$dfn = escape_quotes($this->getDatafilename(), QUOTE_STYLE_SINGLE);
					$string .= " " . $this->getDatafilenOperation() . " datafile '{$dfn}'";
				}
				// [INITIAL_SIZE [=] size]
				if($this->hasInitialSize()){
					$string .= " initial_size " . $this->getInitialSize();
				}
				// [WAIT]
				if($this->getWaitFlag()){
					$string .= " wait";
				}
			}
			// InnoDB and NDB: //[RENAME TO tablespace_name]
			if($this->hasNewName()){
				$string .= " rename to " . $this->getNewName();
			}
			if($engine === STORAGE_ENGINE_INNODB){ // InnoDB only:
			                                         // [AUTOEXTEND_SIZE [=] 'value']
				if($this->hasAutoextendSize()){
					$value = escape_quotes($this->getAutoextendSize(), QUOTE_STYLE_SINGLE);
					$string .= " autoextend_size '{$value}'";
				}
				// [SET {ACTIVE | INACTIVE}]
				if($this->hasActivity()){
					$string .= " set " . $this->getActivity();
				}
				// [ENCRYPTION [=] {'Y' | 'N'}]
				if($this->hasEncryption()){
					$encryption = escape_quotes($this->getEncryption(), QUOTE_STYLE_SINGLE);
					$string .= " encryption '{$encryption}'";
				}
			}
			// InnoDB and NDB: //[ENGINE [=] engine_name]
			$string .= " engine {$engine}";
		}
		return $string;
	}
}
