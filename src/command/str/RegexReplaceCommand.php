<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\LimitedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class RegexReplaceCommand extends StringTransformationCommand
{

	use LimitedTrait;

	protected $count;

	protected $pattern;

	protected $replacement;

	public function __construct($pattern, $replacement, $subject, $limit = - 1)
	{
		parent::__construct($subject);
		$this->setPattern($pattern);
		$this->setReplacement($replacement);
		$this->setLimit($limit);
	}

	public static function getCommandId(): string
	{
		return "preg_replace";
	}

	public function getLimit(): int
	{
		if (! $this->hasLimit()) {
			return - 1;
		}
		return $this->limitCount;
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //RegexReplaceCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$pattern = $this->getPattern();
		if ($pattern instanceof ValueReturningCommandInterface) {
			while ($pattern instanceof ValueReturningCommandInterface) {
				$pattern = $pattern->evaluate();
			}
		}
		$replacement = $this->getReplacement();
		if ($replacement instanceof ValueReturningCommandInterface) {
			while ($replacement instanceof ValueReturningCommandInterface) {
				$replacement = $replacement->evaluate();
			}
		}
		$subject = $this->getSubject();
		if ($subject instanceof ValueReturningCommandInterface) {
			while ($subject instanceof ValueReturningCommandInterface) {
				$subject = $subject->evaluate();
			}
		}
		$limit = $this->getLimit();
		if ($limit instanceof ValueReturningCommandInterface) {
			while ($limit instanceof ValueReturningCommandInterface) {
				$limit = $limit->evaluate();
			}
		}
		$limit = - 1; // javascript string.replace() does not have this argument
		if ($print) {
			Debug::print("{$f} replacing pattern \"{$pattern}\" with \"{$replacement}\" in string \"{$subject}\"");
		}
		return preg_replace($pattern, $replacement, $subject, $limit, $this->count);
	}

	public function toJavaScript(): string
	{
		$subject = $this->getSubject();
		if ($subject instanceof JavaScriptInterface) {
			$subject = $subject->toJavaScript();
		} elseif (is_string($subject) || $subject instanceof StringifiableInterface) {
			$subject = single_quote($subject);
		}
		$pattern = $this->getPattern();
		if ($pattern instanceof JavaScriptInterface) {
			$pattern = $pattern->toJavaScript();
		} /*
		   * elseif(is_string($pattern)){
		   * $pattern = regex_js($pattern);
		   * }
		   */
		// XXX this is only needed for attribute names
		$replacement = $this->getReplacement();
		if ($replacement instanceof JavaScriptInterface) {
			$replacement = $replacement->toJavaScript();
		} elseif (is_string($replacement) || $replacement instanceof StringifiableInterface) {
			$replacement = single_quote($replacement);
		}
		return "{$subject}.replace({$pattern}, {$replacement})";
	}

	public function setPattern($pattern)
	{
		if ($pattern == null) {
			unset($this->pattern);
			return null;
		}
		return $this->pattern = $pattern;
	}

	public function hasPattern(): bool
	{
		return isset($this->pattern);
	}

	public function getPattern()
	{
		$f = __METHOD__; //RegexReplaceCommand::getShortClass()."(".static::getShortClass().")->getPattern()";
		if (! $this->hasPattern()) {
			Debug::error("{$f} pattern is undefined");
		}
		return $this->pattern;
	}

	public function getCount(): int
	{
		if (! $this->hasCount()) {
			return 0;
		}
		return $this->count;
	}

	public function hasCount(): bool
	{
		return isset($this->count);
	}

	public function setReplacement($replacement)
	{
		if ($replacement == null) {
			unset($this->replacement);
			return null;
		}
		return $this->replacement = $replacement;
	}

	public function hasReplacement(): bool
	{
		return isset($this->replacement);
	}

	public function getReplacement()
	{
		$f = __METHOD__; //RegexReplaceCommand::getShortClass()."(".static::getShortClass().")->getReplacement()";
		$print = false;
		if (! $this->hasReplacement()) {
			if ($print) {
				Debug::print("{$f} replacement is not defined");
			}
			return "";
		}
		return $this->replacement;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->count);
		unset($this->limitCount);
		unset($this->pattern);
		unset($this->replacement);
	}
}
