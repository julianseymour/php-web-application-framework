<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\ParentNodeTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class InsertChildCommand extends InsertElementCommand implements JavaScriptInterface, ServerExecutableCommandInterface{

	use ParentNodeTrait;

	public function insertHere($insert_here){
		if($insert_here instanceof Element){
			$this->setParentNode($insert_here);
		}
		return parent::insertHere($insert_here);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasParentNode()){
			$this->setParentNode($that->getParentNode());
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		unset($this->parentNode); //, false, $this->getDebugString());
	}
}