<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class TryCatchCommand extends Command implements JavaScriptInterface, ServerExecutableCommandInterface
{

	public function __construct(...$blocks)
	{
		parent::__construct();
		if (isset($blocks)) {
			$this->setTryBlocks($blocks);
		}
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"resolveCatch"
		]);
	}

	public function setCatchBlocks(?array $blocks): ?array
	{
		return $this->setArrayProperty("catchBlocks", $blocks);
	}

	public function setResolveCatchFlag(bool $value = true): ?bool
	{
		return $this->setFlag("resolveCatch", $value);
	}

	public function getResolveCatchFlag(): bool
	{
		return $this->getFlag("resolveCatch");
	}

	public function resolveCatch(bool $value = true): TryCatchCommand
	{
		$this->setResolveCatchFlag($value);
		return $this;
	}

	public function hasCatchBlocks()
	{
		return $this->hasArrayProperty("catchBlocks");
	}

	public function getCatchBlocks()
	{
		$f = __METHOD__; //TryCatchCommand::getShortClass()."(".static::getShortClass().")->getCatchBlocks()";
		if (! $this->hasCatchBlocks()) {
			Debug::error("{$f} catch blocks are undefined");
		}
		return $this->getProperty("catchBlocks");
	}

	public function setTryBlocks(?array $blocks): ?array
	{
		return $this->setArrayProperty("tryBlocks", $blocks);
	}

	public function hasTryBlocks()
	{
		return $this->hasArrayProperty("tryBlocks");
	}

	public function getTryBlocks()
	{
		$f = __METHOD__; //TryCatchCommand::getShortClass()."(".static::getShortClass().")->getTryBlocks()";
		if (! $this->hasTryBlocks()) {
			Debug::error("{$f} try blocks are undefined");
		}
		return $this->getProperty("tryBlocks");
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //TryCatchCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$string = "";
			$string .= "\ttry{\n";
			if ($this->hasTryBlocks()) {
				foreach ($this->getTryBlocks() as $block) {
					if ($block instanceof JavaScriptInterface) {
						$js = "\t" . $block->toJavaScript();
					} elseif (is_string($block) || $block instanceof StringifiableInterface) {
						$js = $block;
					} else {
						Debug::error("{$f} one of your try blocks cannot be converted to JavaScript");
					}
					$string .= "\t\t{$js};\n";
				}
			}
			$string .= "\t}catch(x){\n";
			if ($this->hasCatchBlocks()) {
				foreach ($this->getCatchBlocks() as $block) {
					if ($block instanceof JavaScriptInterface) {
						$js = $block->toJavaScript();
					} elseif (is_string($block) || $block instanceof StringifiableInterface) {
						$js = $block;
					} else {
						Debug::error("{$f} one of your catch blocks cannot be converted to JavaScript");
					}
					$string .= "\t\t{$js};\n";
				}
				$string .= "\t}\n";
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function try(...$blocks)
	{
		return new TryCatchCommand(...$blocks);
	}

	public function catch(...$catch): TryCatchCommand
	{
		$this->setCatchBlocks($catch);
		return $this;
	}

	public function resolve()
	{
		$f = __METHOD__; //TryCatchCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		try {
			if ($this->getResolveCatchFlag()) {
				foreach ($this->getCatchBlocks() as $block) {
					$block->resolve();
				}
			} else {
				try {
					foreach ($this->getTryBlocks() as $block) {
						$block->resolve();
					}
				} catch (Exception $x) {
					foreach ($this->getCatchBlocks() as $block) {
						$block->resolve();
					}
				}
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getCommandId(): string
	{
		return "try/catch";
	}
}
