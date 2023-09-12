<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class PromiseChainCommand extends Command implements JavaScriptInterface, StaticPropertyTypeInterface
{

	use ExpressionalTrait;
	use StaticPropertyTypeTrait;

	protected $catchHandler;

	public function __construct($expr){
		$f = __METHOD__;
		parent::__construct();
		$this->setExpression($expr);
		if(!$this->hasExpression()) {
			Debug::error("{$f} expression is undefined");
		}
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			'promiseSignatures' => new OrCommand(PromiseSignature::class, JavaScriptFunction::class)
		];
	}

	public function setCatchHandler(?JavaScriptFunction $catch): ?JavaScriptFunction{
		$f = __METHOD__;
		if($catch == null) {
			unset($this->catchHandler);
			return null;
		}elseif(!$catch instanceof JavaScriptFunction) {
			Debug::error("{$f} catch handler must be a javascript function");
		}
		return $this->catchHandler = $catch;
	}

	public function hasCatchHandler(): bool{
		return isset($this->catchHandler) && $this->catchHandler instanceof JavaScriptFunction;
	}

	public function getCatchHandler(): JavaScriptFunction{
		$f = __METHOD__;
		if(!$this->hasCatchHandler()) {
			Debug::error("{$f} catch handler is undefined");
		}
		return $this->catchHandler;
	}

	public function pushPromiseSignatures(...$ps){
		return $this->pushArrayProperty("promiseSignatures", ...$ps);
	}

	public function hasPromiseSignatures(){
		return $this->hasArrayProperty("promiseSignatures");
	}

	public function getPromiseSignatures(){
		return $this->getProperty("promiseSignatures");
	}

	public function then(JavaScriptFunction $fulfilledHandler, ?JavaScriptFunction $rejectedHandler = null): PromiseChainCommand{
		$this->pushPromiseSignatures(new PromiseSignature($fulfilledHandler, $rejectedHandler));
		return $this;
	}

	public function catch($catch): PromiseChainCommand{
		$this->setCatchHandler($catch);
		return $this;
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$expr = $this->getExpression();
		if(is_string($expr)) {
			$expr = single_quote($expr);
		}elseif($expr instanceof JavaScriptInterface) {
			$expr = $expr->toJavaScript();
		}
		$string = "{$expr}";
		foreach($this->getPromiseSignatures() as $settler) {
			$string .= ".then(";
			if($settler instanceof JavaScriptInterface) {
				$string .= $settler->toJavaScript();
			}else{
				Debug::error("{$f} settler is not convertible to javascript");
			}
			$string .= ")";
		}
		if($this->hasCatchHandler()) {
			$string .= ".catch(";
			$catch = $this->getCatchHandler();
			if($catch instanceof JavaScriptInterface) {
				$string .= $catch->toJavaScript();
			}else{
				Debug::error("{$f} catch handler is not convertible to javascript");
			}
		}
		return $string;
	}

	public static function getCommandId(): string{
		return "promiseChain";
	}

	public function setExpression($expr){
		return $this->expression = $expr;
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->catchHandler);
	}

	public function hasExpression(){
		return isset($this->expression);
	}
}
