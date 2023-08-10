<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserLanguagePreference;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\EscapeTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;

/**
 * An object representing a block of code, either a void or value-returning function, which can be
 * executed server-side, converted into an object that is transmitted the the client then executed
 * in their browser by a JavaScript object with the same class name, or converted directly into a
 * string of JavaScript.
 * All three methods should ultimately have the same effect, although not all
 * Commands can be used every way (e.g. there is no meaningful way to execute ScrollIntoViewCommand
 * server-side,
 *
 * @author j
 *        
 */
abstract class Command extends Basic implements CacheableInterface, DisposableInterface, EchoJsonInterface, JavaScriptCounterpartInterface
{

	use ArrayPropertyTrait;
	use CacheableTrait;
	use EchoJsonTrait;
	use EscapeTypeTrait;
	use JavaScriptCounterpartTrait;

	protected $parseType;

	// for GetValue commands
	protected $escapeType;

	protected $quoteStyle;

	public abstract static function getCommandId();

	public function setQuoteStyle($q)
	{
		$f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")->setQuoteStyle()";
		switch ($q) {
			case QUOTE_STYLE_SINGLE:
			case QUOTE_STYLE_DOUBLE:
			case QUOTE_STYLE_BACKTICK:
				return $this->quoteStyle = $q;
			default:
				Debug::error("{$f} invalid quote style \"{$q}\"");
		}
	}

	public function echoJson(bool $destroy = false): void
	{
		$f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")->echoJson()";
		$print = false;
		$cache = false;
		$lang = getCurrentUserLanguagePreference();
		if ($this->isCacheable() && JSON_CACHE_ENABLED) {
			if ($print) {
				Debug::print("{$f} this command is cacheable");
			}
			if (cache()->has($this->getCacheKey() . "_{$lang}.json")) {
				if ($print) {
					Debug::print("{$f} cache hit");
				}
				echo cache()->get($this->getCacheKey() . "_{$lang}.json");
				return;
			} else {
				if ($print) {
					Debug::print("{$f} cache miss");
				}
				$cache = true;
				ob_start();
			}
		} elseif ($print) {
			Debug::print("{$f} this command is not cacheable");
		}
		echo "{";
		$this->echoInnerJson($destroy);
		echo "}";
		if ($cache) {
			if ($print) {
				Debug::print("{$f} about to cache JSON");
			}
			$json = ob_get_clean();
			cache()->set($this->getCacheKey() . "_{$lang}.json", $json, time() + 30 * 60);
			echo $json;
			unset($json);
		} elseif ($print) {
			Debug::print("{$f} nothing to cache");
		}
	}

	public function hasQuoteStyle()
	{
		return isset($this->quoteStyle);
	}

	public function withSubcommands(...$subcommands)
	{
		$this->pushSubcommand(...$subcommands);
		return $this;
	}

	public function getQuoteStyle()
	{
		if (! $this->hasQuoteStyle()) {
			return QUOTE_STYLE_SINGLE;
		}
		return $this->quoteStyle;
	}

	public function getDebugSubcommandString()
	{
		return $this->getCommandId();
	}

	public static function getJavaScriptClassIdentifier(): string
	{
		return static::getCommandId();
	}

	public function debugPrintSubcommands()
	{
		$f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")->debugPrintSubcommands()";
		$string = $this->getDebugSubcommandString();
		// Debug::print("{$f} {$string}");
		if ($this->hasSubcommands()) {
			foreach ($this->getSubcommands() as $sc) {
				$sc->debugPrintSubcommands();
			}
		}
	}

	/*
	 * public static function validateCommands($commands){
	 * $f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")::validateCommands()";
	 * foreach($commands as $c){
	 * if(!$c instanceof Command){
	 * $type = gettype($c);
	 * Debug::error("{$f} one of your commands is a {$type} instead of a media command");
	 * }
	 * }
	 * return SUCCESS;
	 * }
	 */
	public function setParseType($parseType)
	{
		return $this->parseType = $parseType;
	}

	public function hasParseType()
	{
		return isset($this->parseType);
	}

	public function getParseType()
	{
		$f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")->getParseType()";
		if (! $this->hasParseType()) {
			Debug::error("{$f} parse type is undefined");
		}
		return $this->parseType;
	}

	public function reportSubcommand($sc)
	{
		return $this->pushSubcommand($sc);
	}

	public static function linkCommands(...$commands)
	{
		$f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")::linkCommands()";
		$first_command = null;
		$last_command = null;
		foreach ($commands as $command) {
			if (! isset($command)) {
				continue;
			}
			if (is_array($command)) {
				Debug::error("{$f} command is an array");
			}
			if (! isset($first_command)) {
				$first_command = $last_command = $command;
				continue;
			} elseif (isset($last_command)) {
				if (! $last_command instanceof Command) {
					Debug::error("{$f} last command is not a media command");
				}
				$last_command->pushSubcommand($command);
			}
			$last_command = $command;
		}
		if (is_array($first_command)) {
			Debug::error("{$f} first command is an array");
		}
		return $first_command;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //Command::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		$print = false;
		if (app()->getFlag("debug")) {
			Json::echoKeyValuePair("debugId", $this->getDebugId());
			Json::echoKeyValuePair("declared", $this->getDeclarationLine());
		}
		if ($this->hasSubcommands()) {
			Json::echoKeyValuePair('subcommands', $this->getSubcommands(), $destroy);
		}
		if ($this->isOptional()) {
			if ($print) {
				Debug::print("{$f} yes, this command is optional");
			}
			Json::echoKeyValuePair('optional', 1, $destroy);
		} elseif ($print) {
			Debug::print("{$f} no, this command is not optional");
		}
		if ($this->hasParseType()) {
			Json::echoKeyValuePair('parseType', $this->getParseType(), $destroy);
		}
		Json::echoKeyValuePair('command', static::getCommandId(), $destroy, false);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->escapeType);
		unset($this->parseType);
	}

	public function optional(bool $value = true): Command
	{
		$this->setOptional($value);
		return $this;
	}

	public function pushSubcommand(...$subcommands)
	{
		return $this->pushArrayProperty("subcommands", ...$subcommands);
	}

	public function hasSubcommands()
	{
		return $this->hasArrayProperty("subcommands");
	}

	public function getSubcommands()
	{
		return $this->getProperty("subcommands");
	}

	public function mergeSubcommands($values)
	{
		return $this->mergeArrayProperty("subcommands", $values);
	}

	public function setSubcommands($values)
	{
		return $this->setArrayProperty("subcommands", $values);
	}

	public function unshiftSubcommands(...$subcommands)
	{
		return $this->unshiftArrayProperty("subcommands", ...$subcommands);
	}

	public function getSubcommandCount()
	{
		return $this->getArrayPropertyCount("subcommands");
	}

	public function isOptional(): bool
	{
		return $this->getFlag('optional');
	}

	public function setOptional(bool $opt = true): bool
	{
		return $this->setFlag('optional', $opt);
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"optional",
			"resolved"
		]);
	}

	public function toClosure()
	{
		$cmd = $this;
		return function () use ($cmd) {
			$cmd->resolve();
		};
	}

	public function setResolvedFlag($value = true)
	{
		return $this->setFlag("resolved");
	}

	public function getResolvedFlag()
	{
		return $this->getFlag("resolved");
	}
}

//document:
	//readystatechange
//document or element:
	//Scroll
//element:
	//auxclick
	//change
	//contextmenu
	//copy
	//cut
	//dblclick
	//drag
	//dragend
	//dragenter
	//dragleave
	//dragover
	//dragstart
	//drop
	//invalid
	//mousedown
	//mouseenter
	//mouseleave
	//mousemove
	//mouseout
	//mouseover
	//mouseup
	//paste
	//pointerlockchange
	//pointerlockerror
	//select
	//wheel
//element (bubbles up to document):
	//FullScreenChange
	//FullScreenError
//element (media):
	//canplay
	//canplaythrough
	//durationchange
	//emptied
	//ended
	//loadeddata
	//loadedmetadata
	//pause
	//play
	//playing
	//ratechange
	//seeked
	//seeking
	//stalled
	//suspend
	//timeupdate
	//volumechange
	//waiting
	//OfflineAudioContext.complete
//element (no property name):
	//compositionend
	//compositionstart
	//compositionupdate
//window:
	//BeforePrint
	//AfterPrint
	//HashChange
	//Resize
	//Storage
//XHR:
	//abort
	//error
	//load
	//loadend
	//loadstart
	//progress
	//timeout
