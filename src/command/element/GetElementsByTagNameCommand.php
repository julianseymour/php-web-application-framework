<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\element\ElementTagTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetElementsByTagNameCommand extends Command implements JavaScriptInterface
{

	use ElementTagTrait;

	public function __construct($tag)
	{
		parent::__construct();
		$this->setElementTag($tag);
	}

	public static function getCommandId(): string
	{
		return "getElementsByTagName";
	}

	public function toJavaScript(): string
	{
		$tag = $this->getElementTag();
		if ($tag instanceof JavaScriptInterface) {
			$tag = $tag->evaluate();
		} elseif (is_string($tag) || $tag instanceof StringifiableInterface) {
			$tag = single_quote($tag);
		}
		return "document." . $this->getCommandId() . "({$tag})";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->tag);
	}
}
