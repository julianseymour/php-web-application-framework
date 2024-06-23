<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class InsertAfterCommand extends InsertElementCommand implements ServerExecutableCommandInterface{

	public static function getInsertWhere(){
		return "after";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		if($this->hasMultipleElements()){
			Debug::error("{$f} unimplemented: insert multiple elements");
		}
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface){
			$idcs = $idcs->toJavaScript();
		}
		$target = new GetElementByIdCommand($this->getReferenceElementId());
		$target = $target->toJavaScript();
		return "insertAfter({$idcs}, {$target})\n";
	}

	public function __construct($insert_here, ...$elements){
		$f = __METHOD__;
		parent::__construct($insert_here, ...$elements);
		if(!$this->hasReferenceElementId()){
			Debug::error("{$f} ID is undefined");
		}
	}
}
