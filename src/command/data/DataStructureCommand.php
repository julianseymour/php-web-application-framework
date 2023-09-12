<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class DataStructureCommand extends Command implements JavaScriptInterface
{

	use ColumnNameTrait;
	use DataStructuralTrait;

	protected $key;

	protected $idCommand;

	public function __construct($context = null)
	{
		parent::__construct();
		if(isset($context)) {
			$this->setDataStructure($context);
		}
	}

	public function setDataStructure($context)
	{
		$f = __METHOD__; //DataStructureCommand::getShortClass()."(".static::getShortClass().")->setDataStructure()";
		if(is_string($context)) {
			Debug::print("{$f} context is a string, going to assume it's the key");
			$this->setKey($context);
		}elseif($context instanceof DataStructure && $context->hasIdentifierValue()) {
			$this->setKey($context->getIdentifierValue());
		}
		return $this->dataStructure = $context;
	}

	public function setIdCommand($id)
	{
		return $this->idCommand = $id;
	}

	public function hasIdCommand()
	{
		return isset($this->idCommand);
	}

	public function getIdCommandString()
	{
		$f = __METHOD__; //DataStructureCommand::getShortClass()."(".static::getShortClass().")->getIdCommandString()";
		$print = false;
		if(!$this->hasIdCommand()) {
			if($print) {
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} ID command string is undefined, declared {$decl}");
			}
			$context = $this->getDataStructure();
			if($context instanceof ValueReturningCommandInterface) {
				return $context; // ->__toString();
			}
			// Debug::warning("{$f} ID command is undefined");
			return "context";
		}
		// Debug::print("{$f} ID command string is \"{$this->idCommand}\"");
		return $this->idCommand;
	}

	public function setKey($key)
	{
		return $this->key = $key;
	}

	public function hasKey()
	{
		return isset($this->key);
	}

	public function getKey()
	{
		$f = __METHOD__; //DataStructureCommand::getShortClass()."(".static::getShortClass().")->getKey()";
		if(!$this->hasKey()) {
			Debug::error("{$f} key is undefined");
		}
		return $this->key;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //DataStructureCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		if($this->hasKey()) {
			Json::echoKeyValuePair('uniqueKey', $this->getKey(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function getColumnValueCommand($index): GetColumnValueCommand
	{
		return new GetColumnValueCommand($this, $index);
	}

	public function getIdentifierNameCommand(): GetIdentifierNameCommand
	{
		return new GetIdentifierNameCommand($this);
	}

	public function getIdentifierValueCommand(): GetColumnValueCommand
	{
		return new GetColumnValueCommand($this, $this->getIdentifierNameCommand());
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->columnName);
		unset($this->dataStructure);
		unset($this->idCommand);
		unset($this->key);
	}
}
