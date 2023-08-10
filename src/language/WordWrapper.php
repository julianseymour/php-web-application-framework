<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;

abstract class WordWrapper extends Basic implements DisposableInterface, EchoJsonInterface
{

	use EchoJsonTrait;

	protected $word;

	public function __construct($word = null)
	{
		parent::__construct();
		if (! empty($word)) {
			$this->setWord($word);
		}
	}

	public function setWord($word)
	{
		return $this->word = $word;
	}

	public function hasWord()
	{
		return ! empty($this->word);
	}

	public function getWord()
	{
		$f = __METHOD__; //WordWrapper::getShortClass()."(".static::getShortClass().")->getWord()";
		if (! $this->hasWord()) {
			// Debug::warning("{$f} word is undefined");
			return "";
		}
		return $this->word;
	}

	public function __toString(): string
	{
		$word = $this->getWord();
		while ($word instanceof ValueReturningCommandInterface) {
			$word = $word->evaluate();
		}
		return $word;
	}

	public function echoString(bool $destroy = false)
	{
		echo $this->getWord();
		if ($destroy) {
			$this->dispose();
		}
	}

	public function echoJson(bool $destroy = false): void
	{
		echo json_encode($this->getWord());
		if ($destroy) {
			$this->dispose();
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->word);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //WordWrapper::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		ErrorMessage::unimplemented($f);
	}
}
