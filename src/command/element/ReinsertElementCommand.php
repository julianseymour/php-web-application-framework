<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

abstract class ReinsertElementCommand extends ElementCommand implements ServerExecutableCommandInterface{

	use ReferenceElementIdTrait;

	public abstract static function getInsertWhere();

	public static function getCommandId(): string{
		return "reinsert";
	}

	public function __construct($inserted_element, $insert_here){
		$f = __METHOD__;
		parent::__construct($inserted_element);
		if(is_string($insert_here)){
			$this->setReferenceElementId($insert_here);
		}elseif(is_object($insert_here) && $insert_here instanceof Element){
			$this->setReferenceElementId($insert_here->getIdAttribute());
		}else{
			Debug::error("{$f} invalid insertion target");
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Json::echoKeyValuePair('insert_here', $this->getReferenceElementId(), $destroy);
		Json::echoKeyValuePair('where', $this->getInsertWhere(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function resolve(){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasReferenceElementId()){
			$this->setReferenceElementId(replicate($that->getReferenceElementId()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->referenceElementId, $deallocate);
	}
}
