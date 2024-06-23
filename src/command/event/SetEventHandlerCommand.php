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

abstract class SetEventHandlerCommand extends Command implements JavaScriptInterface{

	protected $callFunctionCommand;

	public abstract function getIdCommandString();

	public function __construct($call_function = null){
		$f = __METHOD__;
		parent::__construct();
		if($call_function !== null){
			$this->setCallFunctionCommand($call_function);
		}
	}

	/**
	 *
	 * @param CallFunctionCommand $callFunctionCommand
	 * @return CallFunctionCommand
	 */
	public function setCallFunctionCommand($cf){
		$f = __METHOD__;
		if($this->hasCallFunctionCommand()){
			$this->release($this->callFunctionCommand);
		}
		return $this->callFunctionCommand = $this->claim($cf);
	}

	public function hasCallFunctionCommand():bool{
		return isset($this->callFunctionCommand);
	}

	public function getCallFunctionCommand(){
		$f = __METHOD__;
		if(!$this->hasCallFunctionCommand()){
			Debug::error("{$f} callFunctionCommand is undefined");
		}
		return $this->callFunctionCommand;
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->callFunctionCommand, $deallocate);
	}
	
	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$e = $this->getIdCommandString();
			if($e instanceof JavaScriptInterface){
				$e = $e->toJavaScript();
			}
			$cf = $this->getCallFunctionCommand();
			if($this->hasEscapeType()){
				$pt = $this->getEscapeType();
				switch($pt){
					case ESCAPE_TYPE_FUNCTION:
						if($print){
							Debug::print("{$f} escapeType is function -- wrapping callFunctionCommand in script element");
						}
						$script = new JavaScriptFunction(null, ...$cf->getParameters());
						$script->pushCodeBlock($cf->toJavaScript());
						$cf = $script->toJavaScript();
						break;
					case ESCAPE_TYPE_STRING:
						if($print){
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
			if($command instanceof JavaScriptInterface){
				$command = $command->toJavaScript();
			}
			$string = "{$e}.{$command} = {$cf}";
			// $string .= "\nif(!isset({$e}.{$command})){return error(f, \"{$command} attribute is undefined\");}";
			if($print){
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
