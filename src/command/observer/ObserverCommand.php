<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\observer;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\CallbackTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

abstract class ObserverCommand extends ElementCommand
{

	use CallbackTrait;

	public function __construct($element = null, $callback = null)
	{
		parent::__construct($element);
		if (isset($callback)) {
			$this->setCallback($callback);
		}
	}

	/*
	 * public function getParameterString(){
	 * $callback = $this->getCallback();
	 * return $callback;
	 * }
	 */
	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('callback', $this->getCallback(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->callback);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //ObserverCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$string = "";
			// let observer = new IntersectionObserver(callback, options);
			$mcs = static::getCommandId();
			// $params = $this->getParameterString(true);
			$cb = $this->getCallback();
			if (is_string($cb)) {
				$cb = new GetDeclaredVariableCommand($cb);
			}
			$observer_declared = DeclareVariableCommand::let("observer", new ConstructorCommand($mcs, $cb) // "new {$mcs}({$params})"
			);
			$observer_declared->setScopeType("let");
			$string .= $observer_declared->toJavaScript() . "\n";
			// observer.observe(element);
			$element = $this->getElement();
			if ($element instanceof Element && $element->hasIdOverride()) {
				$e = $element->getIdOverride();
			} else {
				Debug::error("{$f} element needs an ID override");
				$e = new GetElementByIdCommand($element);
			}
			if ($e instanceof JavaScriptInterface) {
				$e = $e->toJavaScript();
			}
			$e = new GetDeclaredVariableCommand($e);
			$observe = new CallFunctionCommand("observer.observe", $e);
			$string .= $observe->toJavaScript() . ";\n"; // "observer.observe({$e});\n";
			                                             // remove observer
			$unobserve = new JavaScriptFunction();
			$unobserve->setRoutineType(ROUTINE_TYPE_FUNCTION);
			$unobserve->pushSubcommand(new CallFunctionCommand("observer.unobserve", $e));
			$remover = DeclareVariableCommand::let("remove", $unobserve);
			$string .= $remover->toJavaScript() . "\n";
			$listener = new CallFunctionCommand("{$e}.addEventListener", "remove_observer", new GetDeclaredVariableCommand("remove"));
			$string .= $listener->toJavaScript();
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

