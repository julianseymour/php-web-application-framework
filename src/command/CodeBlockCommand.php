<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class CodeBlockCommand extends Command implements JavaScriptInterface
{

	public static function getCommandId(): string
	{
		return "{}";
	}

	public function __construct(...$blocks)
	{
		parent::__construct();
		if(isset($blocks) && count($blocks) > 0){
			$this->setSubcommands($blocks);
		}
	}

	public function toJavaScript(): string
	{
		$string = "{\n";
		foreach($this->getSubcommands() as $c){
			if($c instanceof JavaScriptInterface){
				$c = $c->toJavaScript();
			}
			$string .= "\t{$c}\n";
		}
		$string .= "}\n";
		return $string;
	}
}
