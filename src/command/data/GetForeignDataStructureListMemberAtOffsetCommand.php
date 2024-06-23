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

class GetForeignDataStructureListMemberAtOffsetCommand extends ForeignDataStructureCommand implements ValueReturningCommandInterface{

	protected $memberId;

	public function __construct($context, $phylum, $index){
		parent::__construct($context, $phylum);
		$this->setMemberId($index);
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->memberId, $deallocate);
	}
	
	public function setMemberId($index){
		if($this->hasMemberId()){
			$this->release($this->memberId);
		}
		return $this->memberId = $this->claim($index);
	}

	public function hasMemberId():bool{
		return isset($this->memberId);
	}

	public function getMemberId(){
		$f = __METHOD__;
		if(!$this->hasMemberId()){
			Debug::error("{$f} child index is undefined");
		}
		return $this->memberId;
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$idcs = $this->getIdCommandString();
			if($idcs instanceof JavaScriptInterface){
				$idcs = $idcs->toJavaScript();
			}
			$phylum = $this->getColumnName();
			if($phylum instanceof JavaScriptInterface){
				$phylum = $phylum->toJavaScript();
			}elseif(is_string($phylum) || $phylum instanceof StringifiableInterface){
				$phylum = single_quote($phylum);
			}
			$it = $this->getMemberId();
			if($it instanceof GetDeclaredVariableCommand){
				$it = $it->getVariableName();
			}elseif($it instanceof JavaScriptInterface){
				$it = $it->toJavaScript();
			}elseif(is_string($it) || $it instanceof StringifiableInterface){
				$it = single_quote($it);
			}
			return "{$idcs}.getForeignDataStructureListMemberAtOffset({$phylum}, {$it})";
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getCommandId(): string{
		return "getForeignDataStructureListMemberAtOffset";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		try{
			$print = false;
			$iterator = $this->getMemberId();
			while($iterator instanceof GetDeclaredVariableCommand){
				$iterator = $iterator->evaluate();
			}
			$ds = $this->getDataStructure();
			if($ds instanceof ValueReturningCommandInterface){
				while($ds instanceof ValueReturningCommandInterface){
					$ds = $ds->evaluate();
				}
			}
			$column_name = $this->getColumnName();
			if($column_name instanceof ValueReturningCommandInterface){
				while($column_name instanceof ValueReturningCommandInterface){
					$column_name = $column_name->evaluate();
				}
			}
			if(is_object($iterator)){
				$class = $iterator->getClass();
				if($print){
					Debug::print("{$f} possibly after evaluation, child object is an instanceof \"{$class}\"");
				}
				if($iterator instanceof DataStructure){
					if($print){
						Debug::print("{$f} iterator evaluated to a data structure, returning it now");
					}
					return $iterator;
				}elseif($iterator instanceof ValueReturningCommandInterface){
					while($iterator instanceof ValueReturningCommandInterface){
						$iterator = $iterator->evaluate();
					}
				}else{
					Debug::error("{$f} iterator evaluated to a {$class} not recognized by this function");
				}
			}
			if(is_string($iterator)){
				if($print){
					Debug::print("{$f} iterator evaluated to the string \"{$iterator}\"");
				}
				return $ds->getForeignDataStructureListMember($column_name, $iterator);
			}elseif(is_int($iterator)){
				if($print){
					Debug::print("{$f} iterator evaluated to the integer \"{$iterator}\"");
				}
				return $ds->getForeignDataStructureListMemberAtOffset($column_name, $iterator);
			}
			$gottype = gettype($iterator);
			Debug::error("{$f} iterator evaluated to \"{$gottype}\"");
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
