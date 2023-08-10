<?php
namespace JulianSeymour\PHPWebApplicationFramework\language\translate;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ChainTranslateCommand extends TranslateCommand
{

	protected $chain;

	public static function getCommandId(): string
	{
		return "chainTranslate";
	}

	public function __construct($language_id = null, $string_id, ...$chain)
	{
		$f = __METHOD__; //ChainTranslateCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($string_id, $language_id);
		if (! empty($chain)) {
			$arr = [];
			foreach ($chain as $s) {
				array_push($arr, $s);
			}
			$this->setChain($arr);
		} else {
			Debug::error("{$f} please define chain on declaration");
		}
	}

	public function hasChain()
	{
		return ! empty($this->chain);
	}

	public function setChain($chain)
	{
		return $this->chain = $chain;
	}

	public function getChain()
	{
		$f = __METHOD__; //ChainTranslateCommand::getShortClass()."(".static::getShortClass().")->getChain()";
		if (! $this->hasChain()) {
			Debug::error("{$f} chain is undefined");
		}
		return $this->chain;
	}

	public function evaluate(?array $params = null)
	{
		$language_id = $this->getLanguageCode();
		$string_id = $this->getStringId();
		$chain = $this->getChain();
		return Internationalization::chainTranslate($language_id, $string_id, ...$chain);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('chain', $this->chain, $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->chain);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //TranslateCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$string_id = $this->getStringId();
		if ($string_id instanceof JavaScriptInterface) {
			$string_id = $string_id->toJavaScript();
		}
		$cmd = $this->getCommandId();
		$string = "{$cmd}({$string_id}";
		if ($this->hasChain()) {
			$string .= ", [";
			$count = 0;
			foreach ($this->getChain() as $sub) {
				if (is_array($sub)) {
					Debug::error("{$f} subsistution is an array");
				} elseif ($count > 0) {
					$string .= ", ";
				}
				if ($sub instanceof JavaScriptInterface) {
					$sub = $sub->toJavaScript();
				} elseif (is_string($sub) || $sub instanceof StringifiableInterface) {
					$sub = single_quote($sub);
				}
				$string .= $sub;
				$count ++;
			}
			$string .= "]";
		}
		$string .= ")";
		return $string;
	}
}
