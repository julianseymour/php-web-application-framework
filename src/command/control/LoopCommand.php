<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\MultipleElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ParentScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class LoopCommand extends Command implements JavaScriptInterface, ScopedCommandInterface, ServerExecutableCommandInterface
{

	use ArrayPropertyTrait;
	use ParentScopedTrait;
	use ScopedTrait;

	public function __construct(...$blocks)
	{
		parent::__construct();
		if (isset($blocks)) {
			$this->setCodeBlocks($blocks);
		}
	}

	public function setCodeBlocks($blocks)
	{
		return $this->setArrayProperty("codeBlocks", $blocks);
	}

	public function hasCodeBlocks()
	{
		return $this->hasArrayProperty("codeBlocks");
	}

	public function getCodeBlocks()
	{
		return $this->getProperty("codeBlocks");
	}

	public function pushCodeBlocks(...$blocks)
	{
		return $this->pushArrayProperty("codeBlocks", ...$blocks);
	}

	public function getCodeBlockCount()
	{
		return $this->getArrayPropertyCount("codeBlocks");
	}

	/**
	 * do not put this inside a try/catch block because that would break TryCatchCommand->resolve
	 *
	 * @return string
	 */
	public function resolveCodeBlocks()
	{
		$f = __METHOD__; //LoopCommand::getShortClass()."(".static::getShortClass().")->resolveCodeBlocks()";
		$print = false;
		foreach ($this->getCodeBlocks() as $b) {
			if ($print) {
				$bc = $b->getClass();
				Debug::print("{$f} resolving a code block of class \"{$bc}\"");
			}
			if ($b instanceof ElementCommand || $b instanceof MultipleElementCommand) {
				$b->setTemplateLoopFlag(true);
			}
			$b->resolve();
		}
		return SUCCESS;
	}

	public function withCodeBlocks(...$blocks)
	{
		$this->setCodeBlocks($blocks);
		return $this;
	}
}
