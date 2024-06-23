<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class InsertBeforeCommand extends InsertElementCommand implements ServerExecutableCommandInterface{

	use ElementalCommandTrait;

	public static function getInsertWhere(){
		return "before";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$target = $this->getElement();
		$string = "";
		if($target instanceof JavaScriptInterface){
			$nmcs = $target->toJavaScript();
		}else{
			$gottypr = gettype($target);
			Debug::print("{$f} got ttpe \"{$gottypr}\"");
			$nmcs = "near_me";
			$idcs = $this->getIdCommandString();
			$near_me = DeclareVariableCommand::let($nmcs, new GetElementByIdCommand($idcs));
			$string .= $near_me->toJavaScript() . ";\n";
		}
		$string .= "insertBeforeMultiple({$nmcs}, ";
		$i = 0;
		foreach($this->getElements() as $element){
			if($i ++ > 0){
				$string .= ",";
			}
			$ido = $element->getIdOverride();
			if($ido instanceof JavaScriptInterface){
				$ido = $ido->toJavaScript();
			}
			$string .= $ido;
		}
		$string .= ")";
		return $string;
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasElement()){
			$this->setElement(replicate($that->getElement()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->element, $deallocate);
		$this->release($this->id, $deallocate);
	}
}
