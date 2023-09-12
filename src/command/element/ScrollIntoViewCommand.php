<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ScrollIntoViewCommand extends ElementCommand
{

	public static function getCommandId(): string
	{
		return "scrollIntoView";
	}

	public function __construct($element = null, $alignToTop = false)
	{
		parent::__construct($element);
		if($alignToTop) {
			$this->setAlignToTop($alignToTop);
		}
	}

	/**
	 * instantly scroll an element with smooth scrolling behavor and reset it to smooth scroll
	 *
	 * @param Element $scrolled
	 * @param Element $scroll_me
	 * @return SetStylePropertiesCommand
	 */
	public static function getInstantScrollCommand($scrolled, $scroll_me, bool $alignToTop = false)
	{
		$auto = new SetStylePropertiesCommand($scrolled, [
			"scroll-behavior" => "auto"
		]);
		$scroll = new ScrollIntoViewCommand($scroll_me, $alignToTop);
		$smooth = new SetStylePropertiesCommand($scrolled, [
			"scroll-behavior" => "smooth"
		]);
		return static::linkCommands($auto, $scroll, $smooth);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		if($this->getAlignToTop()) {
			Json::echoKeyValuePair('alignToTop', true, $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function getAlignToTop()
	{
		return $this->getFlag('alignToTop');
	}

	public function setAlignToTop($value)
	{
		return $this->setFlag("alignToTop", $value);
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		$align = $this->getAlignToTop() ? "true" : "false";
		return "{$idcs}.scrollIntoView({$align})";
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"alignToTop"
		]);
	}
}
