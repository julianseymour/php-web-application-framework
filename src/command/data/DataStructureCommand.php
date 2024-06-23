<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class DataStructureCommand extends Command implements JavaScriptInterface{

	use ColumnNameTrait;
	use DataStructuralTrait;

	protected $key;

	protected $idCommand;

	public function __construct($context = null){
		parent::__construct();
		if($context !== null){
			$this->setDataStructure($context);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasKey()){
			$this->setKey(replicate($that->getKey()));
		}
		if($that->hasIdCommand()){
			$this->setIdCommand(replicate($that->getIdCommandString()));
		}
		if($that->hasDataStructure()){
			$this->setDataStructure($that->getDataStructure());
		}
		if($that->hasColumnName()){
			$this->setColumnName(replicate($that->getColumnName()));
		}
		return $ret;
	}
	
	public function setDataStructure($context){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(is_string($context)){
			if($print){
				Debug::print("{$f} context is a string, going to assume it's the key");
			}
			return $this->setKey($context);
		}elseif($this->hasDataStructure()){
			$this->releaseDataStructure();
		}
		if($context instanceof DataStructure && $context->hasIdentifierValue()){
			$this->setKey($context->getIdentifierValue());
		}
		if($context instanceof HitPointsInterface){
			$this->addDataStructureDeallocateListener($context);
		}
		return $this->dataStructure = $this->claim($context);
	}

	public function setIdCommand($id){
		if($this->hasIdCommand()){
			$this->release($this->idCommand);
		}
		return $this->idCommand = $this->claim($id);
	}

	public function hasIdCommand():bool{
		return isset($this->idCommand);
	}

	public function getIdCommandString(){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasIdCommand()){
			if($print){
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} ID command string is undefined, declared {$decl}");
			}
			$context = $this->getDataStructure();
			if($context instanceof ValueReturningCommandInterface){
				return $context;
			}
			// Debug::warning("{$f} ID command is undefined");
			return "context";
		}
		// Debug::print("{$f} ID command string is \"{$this->idCommand}\"");
		return $this->idCommand;
	}

	public function setKey($key){
		if($this->hasKey()){
			$this->release($this->key);
		}
		return $this->key = $this->claim($key);
	}

	public function hasKey():bool{
		return isset($this->key);
	}

	public function getKey(){
		$f = __METHOD__;
		if(!$this->hasKey()){
			Debug::error("{$f} key is undefined");
		}
		return $this->key;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		if($this->hasKey()){
			Json::echoKeyValuePair('uniqueKey', $this->getKey(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function getColumnValueCommand($index): GetColumnValueCommand{
		return new GetColumnValueCommand($this, $index);
	}

	public function getIdentifierNameCommand(): GetIdentifierNameCommand{
		return new GetIdentifierNameCommand($this);
	}

	public function getIdentifierValueCommand(): GetColumnValueCommand{
		return new GetColumnValueCommand($this, $this->getIdentifierNameCommand());
	}

	public function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		if($this->hasDataStructure()){
			/*if(!$this->dataStructure->getAllocatedFlag()){
				Debug::error("{$f} data structure is not allocated for this ".$this->getDebugString());
			}*/
			$this->releaseDataStructure($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->idCommand, $deallocate);
		$this->release($this->key, $deallocate);
	}
}
