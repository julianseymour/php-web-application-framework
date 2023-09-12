<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ReinsertBeforeCommand extends ReinsertElementCommand
{

	public static function getCommandId(): string
	{
		return "reinsert";
	}

	public static function getInsertWhere()
	{
		return CONST_BEFORE;
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		$insert_here = DeclareVariableCommand::let("__insert_here", new GetElementByIdCommand($this->getReferenceElementId()));
		$insert_here = $insert_here->toJavaScript();
		return "{$insert_here};\n__insert_here.parentNode.insertBefore({$idcs}, __insert_here)";
	}
}
