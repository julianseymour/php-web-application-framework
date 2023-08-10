<?php
namespace JulianSeymour\PHPWebApplicationFramework\core;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\getExecutionTime;
use function JulianSeymour\PHPWebApplicationFramework\get_file_line;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StatusTrait;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationRuntime;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationConfiguration;

/**
 * This class defines very generic behavior that is used by most classes
 *
 * @author j
 *        
 */
abstract class Basic
{

	use FlagBearingTrait;
	use StatusTrait;

	protected $debugId;

	protected $declarationLine;

	public function __construct(){
		$f = __METHOD__;
		if (getExecutionTime(true) > 7.0) {
			// Debug::error("{$f} execution time is greater than 7 seconds");
		}
		$print = false;
		$this->setAllocatedFlag(true);
		if (
			!$this instanceof ApplicationRuntime &&
			!$this instanceof ApplicationConfiguration && 
			app()->getFlag("debug")) {
			$this->setDebugId(sha1(random_bytes(32)));
			$decl = get_file_line([
				"__construct"
			], 7);
			if ($print) {
				Debug::print("{$f} declared \"{$decl}\"");
			}
			$this->setDeclarationLine($decl);
			if ($print) {
				Debug::print("{$f} constructed object with debug ID \"{$this->debugId}\"");
			}
		}
	}

	public function setAllocatedFlag(bool $value = true): bool{
		return $this->setFlag("allocated", $value);
	}

	public function getAllocatedFlag(): bool{
		return $this->getFlag("allocated");
	}

	public function setDeclarationLine($dl): ?string{
		$f = __METHOD__; //Basic::getShortClass()."(".static::getShortClass().")->setDeclarationLine()";
		if ($dl == null) {
			unset($this->declarationLine);
			return null;
		}
		return $this->declarationLine = $dl;
	}

	public function hasDeclarationLine(): bool{
		return isset($this->declarationLine);
	}

	public function getDeclarationLine(): string{
		$f = __METHOD__; //Basic::getShortClass()."(".static::getShortClass().")->getDeclarationLine()";
		$print = false;
		if (! $this->hasDeclarationLine()) {
			if ($print) {
				Debug::warning("{$f} declaration line is undefined");
			}
			return "undefined";
		}
		return $this->declarationLine;
	}

	public static function declareFlags(): ?array{
		return [
			"allocated",
			"debug"
		];
	}

	public function getDebugFlag(): bool{
		return $this->getFlag("debug");
	}

	public function setDebugFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setFlag("debug", $value);
	}

	public function debug(bool $value = true): Basic{
		$f = __METHOD__;
		$this->setDebugFlag($value);
		return $this;
	}

	protected function setDebugId($id){
		return $this->debugId = $id;
	}

	public static final function getClass(): string{
		return static::class;
	}

	public function __destruct(){
		$this->dispose();
	}

	public function dispose(): void
	{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if (app()->getFlag("debug")) {
				Debug::printStackTraceNoExit("{$f} entered; debug ID is \"{$this->debugId}\"; declared {$this->declarationLine}");
			} else {
				Debug::printStackTraceNoExit("{$f} entered");
			}
		}
		// unset($this->debugId);
		// unset($this->declarationLine);
		unset($this->flags);
		unset($this->status);
		unset($this->undeclaredFlags);
	}

	public function hasDebugId(){
		return isset($this->debugId);
	}

	public function getDebugId(){
		return $this->debugId;
	}

	public static function getShortClass(): string{
		return get_short_class(static::class);
	}
}
