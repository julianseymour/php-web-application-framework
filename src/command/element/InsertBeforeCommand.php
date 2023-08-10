<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class InsertBeforeCommand extends InsertElementCommand implements ServerExecutableCommandInterface
{

	use ElementalCommandTrait;

	public function __construct($insert_here, ...$inserted_elements)
	{
		$f = __METHOD__; //InsertBeforeCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($insert_here, ...$inserted_elements);
		if (! $this->hasReferenceElementId() && ! $this->hasElement()) {
			Debug::error("{$f} element and element ID are undefined");
		}
	}

	public static function getInsertWhere()
	{
		return "before";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //InsertBeforeCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		/*
		 * if($this->getElementCount() > 1){
		 * $elements = $this->getElements();
		 * foreach($elements as $element){
		 * Debug::print("{$f} ".$element->getClass());
		 * }
		 * Debug::error("{$f} unimplemented: insert multiple elements");
		 * }
		 */
		// $idcs = $this->getIdCommandString();
		$target = $this->getElement();
		$string = "";
		if ($target instanceof JavaScriptInterface) {
			$nmcs = $target->toJavaScript();
		} else {
			$gottypr = gettype($target);
			Debug::print("{$f} got ttpe \"{$gottypr}\"");
			$nmcs = "near_me";
			$idcs = $this->getIdCommandString();
			/*
			 * if("{$idcs}" == ""){
			 * Debug::error("{$f} ID command string is empty");
			 * }
			 * Debug::print("{$f} ID command string is \"{$idcs}\"");
			 */
			$near_me = DeclareVariableCommand::let($nmcs, new GetElementByIdCommand($idcs));
			$string .= $near_me->toJavaScript() . ";\n";
		}
		$string .= "insertBeforeMultiple({$nmcs}, ";
		$i = 0;
		foreach ($this->getElements() as $element) {
			if ($i ++ > 0) {
				$string .= ",";
			}
			$ido = $element->getIdOverride();
			if ($ido instanceof JavaScriptInterface) {
				$ido = $ido->toJavaScript();
			}
			$string .= $ido;
		}
		$string .= ")";
		return $string;
	}
}
