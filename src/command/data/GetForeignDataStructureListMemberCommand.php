<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class GetForeignDataStructureListMemberCommand extends ForeignDataStructureCommand implements ValueReturningCommandInterface
{

	protected $memberId;

	public function __construct($context, $phylum, $index)
	{
		parent::__construct($context, $phylum);
		$this->setMemberId($index);
	}

	public function setMemberId($index)
	{
		$f = __METHOD__; //GetForeignDataStructureListMemberCommand::getShortClass()."(".static::getShortClass().")->setMemberId()";
		$print = false;
		if($print) {
			if(is_object($index)) {
				$class = $index->getClass();
				Debug::print("{$f} child index is an object of class \"{$class}\"");
			}else{
				$gottype = gettype($index);
				Debug::print("{$f} child index type is \"{$gottype}\"");
			}
		}
		return $this->memberId = $index;
	}

	public function hasMemberId()
	{
		return isset($this->memberId);
	}

	public function getMemberId()
	{
		$f = __METHOD__; //GetForeignDataStructureListMemberCommand::getShortClass()."(".static::getShortClass().")->getMemberId()";
		if(!$this->hasMemberId()) {
			Debug::error("{$f} child index is undefined");
		}
		return $this->memberId;
	}

	public static function getCommandId(): string
	{
		return "getChild";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //GetForeignDataStructureListMemberCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		try{
			$print = false;
			$iterator = $this->getMemberId();
			while ($iterator instanceof GetDeclaredVariableCommand) {
				$iterator = $iterator->evaluate();
			}
			$ds = $this->getDataStructure();
			if($ds instanceof ValueReturningCommandInterface) {
				while ($ds instanceof ValueReturningCommandInterface) {
					$ds = $ds->evaluate();
				}
			}
			$column_name = $this->getColumnName();
			if($column_name instanceof ValueReturningCommandInterface) {
				while ($column_name instanceof ValueReturningCommandInterface) {
					$column_name = $column_name->evaluate();
				}
			}
			if(is_object($iterator)) {
				$class = $iterator->getClass();
				if($print) {
					Debug::print("{$f} possibly after evaluation, child object is an instanceof \"{$class}\"");
				}
				if($iterator instanceof DataStructure) {
					if($print) {
						Debug::print("{$f} iterator evaluated to a data structure, returning it now");
					}
					return $iterator;
				}elseif($iterator instanceof ValueReturningCommandInterface) {
					while ($iterator instanceof ValueReturningCommandInterface) {
						$iterator = $iterator->evaluate();
					}
				}else{
					Debug::error("{$f} iterator evaluated to a {$class} not recognized by this function");
				}
			}
			if(is_string($iterator)) {
				if($print) {
					Debug::print("{$f} iterator evaluated to the string \"{$iterator}\"");
				}
				return $ds->getForeignDataStructureListMember($column_name, $iterator);
			}elseif(is_int($iterator)) {
				if($print) {
					Debug::print("{$f} iterator evaluated to the integer \"{$iterator}\"");
				}
				return $ds->getForeignDataStructureListMemberAtOffset($column_name, $iterator);
			}
			$gottype = gettype($iterator);
			Debug::error("{$f} iterator evaluated to \"{$gottype}\"");
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //GetForeignDataStructureListMemberCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try{
			$idcs = $this->getIdCommandString();
			if($idcs instanceof JavaScriptInterface) {
				$idcs = $idcs->toJavaScript();
			}
			$phylum = $this->getColumnName();
			if($phylum instanceof JavaScriptInterface) {
				$phylum = $phylum->toJavaScript();
			}elseif(is_string($phylum) || $phylum instanceof StringifiableInterface) {
				$phylum = single_quote($phylum);
			}
			$it = $this->getMemberId();
			if($it instanceof GetDeclaredVariableCommand) {
				$it = $it->getVariableName();
			}elseif($it instanceof JavaScriptInterface) {
				$it = $it->toJavaScript();
			}elseif(is_string($it) || $it instanceof StringifiableInterface) {
				$it = single_quote($it);
			}
			return "{$idcs}.getForeignDataStructureListMember({$phylum}, {$it})";
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/*
	 * public function getIdentifierNameCommand(){
	 * return new GetIdentifierNameCommand($this);
	 * }
	 *
	 * public function GetColumnValueCommand($column_name){
	 * return new GetColumnValueCommand($this, $column_name);
	 * }
	 */
	public function dispose(): void
	{
		parent::dispose();
		unset($this->memberId);
	}
}
