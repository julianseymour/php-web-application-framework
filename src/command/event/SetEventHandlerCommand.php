<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

abstract class SetEventHandlerCommand extends Command implements JavaScriptInterface
{

	protected $callFunctionCommand;

	public abstract function getIdCommandString();

	public function __construct($call_function = null)
	{
		$f = __METHOD__; //SetEventHandlerCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		if(isset($call_function)) {
			$this->setCallFunctionCommand($call_function);
		}
	}

	/**
	 *
	 * @param CallFunctionCommand $callFunctionCommand
	 * @return CallFunctionCommand
	 */
	public function setCallFunctionCommand($cf)
	{
		$f = __METHOD__; //SetEventHandlerCommand::getShortClass()."(".static::getShortClass().")->setCallFunctionCommand()";
		/*
		 * if(!$cf instanceof CallFunctionCommand && !$cf instanceof JavaScriptFunction){
		 * $gottype = is_object($cf) ? $cf->getClass() : gettype($cf);
		 * Debug::error("{$f} this function requires a call function media command; received a {$gottype}");
		 * }
		 */
		return $this->callFunctionCommand = $cf;
	}

	public function hasCallFunctionCommand()
	{
		return isset($this->callFunctionCommand);
	}

	public function getCallFunctionCommand()
	{
		$f = __METHOD__; //SetEventHandlerCommand::getShortClass()."(".static::getShortClass().")->getCallFunctionCommand()";
		if(!$this->hasCallFunctionCommand()) {
			Debug::error("{$f} callFunctionCommand is undefined");
		}
		return $this->callFunctionCommand;
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //SetEventHandlerCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try{
			$print = false;
			$e = $this->getIdCommandString();
			if($e instanceof JavaScriptInterface) {
				$e = $e->toJavaScript();
			}
			$cf = $this->getCallFunctionCommand();
			if($this->hasEscapeType()) {
				$pt = $this->getEscapeType();
				switch ($pt) {
					case ESCAPE_TYPE_FUNCTION:
						if($print) {
							Debug::print("{$f} escapeType is function -- wrapping callFunctionCommand in script element");
						}
						$script = new JavaScriptFunction(null, ...$cf->getParameters());
						$script->pushSubcommand($cf->toJavaScript());
						$cf = $script->toJavaScript();
						break;
					case ESCAPE_TYPE_STRING:
						if($print) {
							Debug::print("{$f} escape type string");
						}
						$q = $this->getQuoteStyle();
						$cf = escape_quotes($cf, $q);
						$cf = "{$q}{$cf}{$q}";
						break;
					default:
						Debug::error("{$f} invalid escape type \"{$pt}\"");
						break;
				}
			}
			$command = $this->getCommandId();
			if($command instanceof JavaScriptInterface) {
				$command = $command->toJavaScript();
			}
			$string = "{$e}.{$command} = {$cf}";
			// $string .= "\nif(!isset({$e}.{$command})){return error(f, \"{$command} attribute is undefined\");}";
			if($print) {
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
