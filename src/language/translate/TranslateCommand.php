<?php
namespace JulianSeymour\PHPWebApplicationFramework\language\translate;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsSessionData;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class TranslateCommand extends Command implements JavaScriptInterface, StringifiableInterface, ValueReturningCommandInterface
{

	protected $stringId;

	protected $languageCode;

	protected $substitutions;

	public static function getCommandId(): string
	{
		return "translate";
	}

	public function __toString(): string
	{
		return $this->evaluate();
	}

	public function __construct($string_id, $language_id = null, ...$substitutions)
	{
		$f = __METHOD__; //TranslateCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		if (! isset($string_id)) {
			Debug::error("{$f} string ID is undefined");
		} elseif ($language_id !== null) {
			$this->setLanguageCode($language_id);
		}

		$this->setStringId($string_id);
		if (! $this->hasStringId()) {
			Debug::error("{$f} after setting string ID it is still undefined");
		}
		if (! empty($substitutions)) {
			$arr = [];
			foreach ($substitutions as $sub) {
				if (is_array($sub)) {
					Debug::error("{$f} one of your substitutions is an array");
				}
				array_push($arr, $sub);
			}
			$this->setSubstitutions($arr);
		}
	}

	public function hasStringId()
	{
		return isset($this->stringId);
	}

	public function setStringId($string_id)
	{
		return $this->stringId = $string_id;
	}

	public function getStringId()
	{
		$f = __METHOD__; //TranslateCommand::getShortClass()."(".static::getShortClass().")->getStringId()";
		if (! $this->hasStringId()) {
			Debug::error("{$f} string ID is undefined");
		}
		return $this->stringId;
	}

	public function hasLanguageCode()
	{
		return isset($this->languageCode);
	}

	public function setLanguageCode($language_id)
	{
		$f = __METHOD__; //TranslateCommand::getShortClass()."(".static::getShortClass().")->setLanguageCode()";
		if (! config()->isLanguageSupported($language_id)) {
			Debug::error("{$f} unsupported language ID \"{$language_id}\"");
		}
		return $this->languageCode = $language_id;
	}

	public function getLanguageCode()
	{
		$f = __METHOD__; //TranslateCommand::getShortClass()."(".static::getShortClass().")->getLanguageCode()";
		$print = false;
		if (! $this->hasLanguageCode()) {
			if (app()->hasLanguageOverride() || app()->hasUserData()) {
				if ($print) {
					Debug::print("{$f} application instance has a language override defined");
				}
				return app()->getCurrentUserLanguagePreference();
			} elseif ($print) {
				Debug::print("{$f} application instance does not define a language override");
			}
			$lssd = new LanguageSettingsSessionData();
			return $lssd->getLanguageCode();
		} elseif ($print) {
			Debug::print("{$f} language code is explicitly defined for this command as \"{$this->languageCode}\"");
		}
		return $this->languageCode;
	}

	public function hasSubstitutions()
	{
		return ! empty($this->substitutions);
	}

	public function getSubstitutions()
	{
		return $this->substitutions;
	}

	public function setSubstitutions($substitutions)
	{
		return $this->substitutions = $substitutions;
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //TranslateCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$string_id = $this->getStringId();
		$language_id = $this->getLanguageCode();
		if ($language_id instanceof ValueReturningCommandInterface) {
			while ($language_id instanceof ValueReturningCommandInterface) {
				$language_id = $language_id->evaluate();
			}
		}
		$substitutions = $this->hasSubstitutions() ? $this->getSubstitutions() : [];
		$value = Internationalization::translate($string_id, $language_id, ...$substitutions);
		// Debug::print("{$f} returning \"{$value}\"");
		return $value;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('stringId', $this->getStringId(), $destroy);
		if ($this->hasSubstitutions()) {
			Json::echoKeyValuePair('substitutions', $this->substitutions, $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->languageCode);
		unset($this->stringId);
		unset($this->substitutions);
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
		if ($this->hasSubstitutions()) {
			$string .= ", [";
			$count = 0;
			foreach ($this->getSubstitutions() as $sub) {
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
