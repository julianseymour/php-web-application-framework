<?php

namespace JulianSeymour\PHPWebApplicationFramework\command;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\EscapeTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;

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
abstract class Command extends Basic implements CacheableInterface, DisposableInterface, EchoJsonInterface, JavaScriptCounterpartInterface, ReplicableInterface{

	use ArrayPropertyTrait;
	use CacheableTrait;
	use EchoJsonTrait;
	use EscapeTypeTrait;
	use JavaScriptCounterpartTrait;
	use ReplicableTrait; //XXX TODO the only command set up to handle replication properly is GetDeclaredVariable

	protected $parseType;

	protected $quoteStyle;

	public abstract static function getCommandId();

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasParseType()){
			$this->setParseType(replicate($that->getParseType()));
		}
		if($that->hasEscapeType()){
			$this->setEscapeType(replicate($that->getEscapeType()));
		}
		if($that->hasQuoteStyle()){
			$this->setQuoteStyle(replicate($that->getQuoteStyle()));
		}
		$this->copyProperties($that);
		return $ret;
	}
	
	public function setQuoteStyle($q){
		$f = __METHOD__;
		if($this->hasQuoteStyle()){
			$this->release($this->quoteStyle);
		}
		switch($q){
			case QUOTE_STYLE_SINGLE:
			case QUOTE_STYLE_DOUBLE:
			case QUOTE_STYLE_BACKTICK:
				break;
			default:
				Debug::error("{$f} invalid quote style \"{$q}\"");
		}
		return $this->quoteStyle = $this->claim($q);
	}

	public function echoJson(bool $destroy = false): void{
		$f = __METHOD__;
		$print = false;
		$cache = false;
		$locale = user()->getLocaleString();
		if($this->isCacheable() && JSON_CACHE_ENABLED){
			if($print){
				Debug::print("{$f} this command is cacheable");
			}
			if(cache()->has($this->getCacheKey() . "_{$locale}.json")){
				if($print){
					Debug::print("{$f} cache hit");
				}
				echo cache()->get($this->getCacheKey() . "_{$locale}.json");
				return;
			}else{
				if($print){
					Debug::print("{$f} cache miss");
				}
				$cache = true;
				ob_start();
			}
		}elseif($print){
			Debug::print("{$f} this command is not cacheable");
		}
		echo "{";
		$this->echoInnerJson($destroy);
		echo "}";
		if($cache){
			if($print){
				Debug::print("{$f} about to cache JSON");
			}
			$json = ob_get_clean();
			cache()->set($this->getCacheKey() . "_{$locale}.json", $json, time() + 30 * 60);
			echo $json;
			unset($json);
		}elseif($print){
			Debug::print("{$f} nothing to cache");
		}
	}

	public function hasQuoteStyle():bool{
		return isset($this->quoteStyle);
	}

	public function withSubcommands(...$subcommands){
		$this->pushSubcommand(...$subcommands);
		return $this;
	}

	public function getQuoteStyle(){
		if(!$this->hasQuoteStyle()){
			return QUOTE_STYLE_SINGLE;
		}
		return $this->quoteStyle;
	}

	public function getDebugSubcommandString(){
		return $this->getCommandId();
	}

	public static function getJavaScriptClassIdentifier(): string{
		return static::getCommandId();
	}

	public function debugPrintSubcommands(){
		$f = __METHOD__;
		$string = $this->getDebugSubcommandString();
		// Debug::print("{$f} {$string}");
		if($this->hasSubcommands()){
			foreach($this->getSubcommands() as $sc){
				$sc->debugPrintSubcommands();
			}
		}
	}

	public function setParseType($parseType){
		if($this->hasParseType()){
			$this->release($this->parseType);
		}
		return $this->parseType = $this->claim($parseType);
	}

	public function hasParseType():bool{
		return isset($this->parseType);
	}

	public function getParseType(){
		$f = __METHOD__;
		if(!$this->hasParseType()){
			Debug::error("{$f} parse type is undefined");
		}
		return $this->parseType;
	}

	public function reportSubcommand($sc){
		return $this->pushSubcommand($sc);
	}

	public static function linkCommands(...$commands){
		$f = __METHOD__;
		$first_command = null;
		$last_command = null;
		foreach($commands as $command){
			if(!isset($command)){
				continue;
			}
			if(is_array($command)){
				Debug::error("{$f} command is an array");
			}
			if(!isset($first_command)){
				$first_command = $last_command = $command;
				continue;
			}elseif(isset($last_command)){
				if(!$last_command instanceof Command){
					Debug::error("{$f} last command is not a media command");
				}
				$last_command->pushSubcommand($command);
			}
			$last_command = $command;
		}
		if(is_array($first_command)){
			Debug::error("{$f} first command is an array");
		}
		return $first_command;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		$print = false;
		if(false && app()->getFlag("debug")){
			Json::echoKeyValuePair("debugId", $this->getDebugId());
			Json::echoKeyValuePair("declared", $this->getDeclarationLine());
		}
		if($this->hasSubcommands()){
			Json::echoKeyValuePair('subcommands', $this->getSubcommands(), $destroy);
		}
		if($this->isOptional()){
			if($print){
				Debug::print("{$f} yes, this command is optional");
			}
			Json::echoKeyValuePair('optional', 1, $destroy);
		}elseif($print){
			Debug::print("{$f} no, this command is not optional");
		}
		if($this->hasParseType()){
			Json::echoKeyValuePair('parseType', $this->getParseType(), $destroy);
		}
		Json::echoKeyValuePair('command', static::getCommandId(), $destroy, false);
	}

	public function optional(bool $value = true): Command{
		$this->setOptional($value);
		return $this;
	}

	public function pushSubcommand(...$subcommands):int{
		return $this->pushArrayProperty("subcommands", ...$subcommands);
	}

	public function hasSubcommands():bool{
		return $this->hasArrayProperty("subcommands");
	}

	public function getSubcommands(){
		return $this->getProperty("subcommands");
	}

	public function mergeSubcommands($values){
		return $this->mergeArrayProperty("subcommands", $values);
	}

	public function setSubcommands($values){
		return $this->setArrayProperty("subcommands", $values);
	}

	public function unshiftSubcommands(...$subcommands){
		return $this->unshiftArrayProperty("subcommands", ...$subcommands);
	}

	public function getSubcommandCount():int{
		return $this->getArrayPropertyCount("subcommands");
	}

	public function isOptional():bool{
		return $this->getFlag('optional');
	}

	public function setOptional(bool $opt = true): bool{
		return $this->setFlag('optional', $opt);
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"optional",
			"resolved"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"optional"
		]);
	}
	
	public function toClosure(){
		$cmd = $this;
		return function() use ($cmd){
			$cmd->resolve();
		};
	}

	public function setResolvedFlag(bool $value = true):bool{
		return $this->setFlag("resolved");
	}

	public function getResolvedFlag():bool{
		return $this->getFlag("resolved");
	}
	
	public function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasProperties()){
			if($print){
				Debug::print("{$f} about to release properties for this ".$this->getDebugString());
			}
			$this->releaseProperties($deallocate);
		}elseif($print){
			Debug::print("{$f} no properties to release for this ".$this->getDebugString());
		}
		if($print){
			Debug::print("{$f} about to call parent function for ".$this->getDebugString());
		}
		parent::dispose($deallocate);
		if($print){
			Debug::print("{$f} returning from parent function for ".$this->getDebugString());
		}
		$this->release($this->cacheKey, $deallocate);
		$this->release($this->timeToLive, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		$this->release($this->escapeType, $deallocate);
		$this->release($this->parseType, $deallocate);
		if($print){
			Debug::print("{$f} returning for ".$this->getDebugString());
		}
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
