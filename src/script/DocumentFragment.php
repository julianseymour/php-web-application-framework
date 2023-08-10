<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

class DocumentFragment extends Element
{

	/*
	 * public function getTemplateFunctionCommands($parent_name, &$counter){
	 * $f = __METHOD__; //DocumentFragment::getShortClass()."(".static::getShortClass().")->getTemplateFunctionCommands()";
	 * try{
	 * //$counter++;
	 * if(!$this->hasIdOverride()){
	 * $this->setIdOverride("fragment{$counter}");
	 * }
	 * $commands = [DeclareVariableCommand::let($this, $this)];
	 * $reserved = [];
	 * foreach($this->getChildNodes() as $child){
	 * $ido = $child->getIdOverride();
	 * if(array_key_exists($ido, $reserved)){
	 * Debug::error("{$f} ID override \"{$ido}\" has already been used");
	 * }
	 * $reserved[$ido] = $child;
	 * array_push($commands, new AppendChildCommand($this, $child));
	 * }
	 * return $commands;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public function echo(bool $destroy = false): void
	{
		echo "new DocumentFragment()";
	}

	public static function getElementTagStatic(): string
	{
		return "fragment";
	}
}
