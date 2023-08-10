<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ReidentifyElementCommand extends ElementCommand implements ServerExecutableCommandInterface{

	protected $newId;

	public function __construct($element = null, $newId = null){
		parent::__construct($element);
		if ($newId !== null) {
			$this->setNewId($newId);
		}
	}

	public static function getCommandId(): string{
		return "reidentify";
	}

	public function hasNewId(){
		return ! empty($this->newId);
	}

	public function getNewId(){
		$f = __METHOD__;
		if (! $this->hasNewId()) {
			Debug::error("{$f} new ID is undefined");
		}
		return $this->newId;
	}

	public function setNewId($newId){
		if ($newId === null) {
			unset($this->newId);
			return null;
		}
		return $this->newId = $newId;
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('new_id', $this->getNewId(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->newId);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$print = false;
		$id = $this->getIdCommandString();
		if ($id instanceof JavaScriptInterface) {
			$id = $id->toJavaScript();
		}
		$new_id = $this->getNewId();
		if ($new_id instanceof JavaScriptInterface) {
			$new_id = $new_id->toJavaScript();
		} elseif (is_string($new_id) || $new_id instanceof StringifiableInterface) {
			if ($this->hasQuoteStyle()) {
				$qs = $this->getQuoteStyle();
			} else {
				$qs = QUOTE_STYLE_SINGLE;
			}
			$new_id = $qs . escape_quotes($new_id, $this->getQuoteStyle()) . $qs;
		}
		if ($print) {
			if ($new_id instanceof ValueReturningCommandInterface) {
				$cc = $new_id->getClass();
				Debug::print("{$f} new ID command class is \"{$cc}\"; stringified it't \"{$new_id}\"");
			} else {
				Debug::print("{$f} new ID is NOT and was never a command, it's \"{$new_id}\"");
			}
		}
		$string = "{$id}.id = {$new_id}";
		return $string;
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$new_id = $this->getNewId();
		while ($new_id instanceof ValueReturningCommandInterface) {
			$new_id = $new_id->evaluate();
		}
		$element->setIdAttribute($new_id);
	}
}
