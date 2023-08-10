<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class ShowNotificationCommand extends Command
{

	protected $title;

	protected $options;

	public function hasTitle()
	{
		return ! empty($this->title);
	}

	public function getTitle()
	{
		$f = __METHOD__; //ShowNotificationCommand::getShortClass()."(".static::getShortClass().")->getTitle()";
		if (! $this->hasTitle()) {
			Debug::error("{$f} title is undefined");
		}
		return $this->title;
	}

	public function setTitle($title)
	{
		return $this->title = $title;
	}

	public function hasOptions()
	{
		return ! empty($this->options);
	}

	public function getOptions()
	{
		$f = __METHOD__; //ShowNotificationCommand::getShortClass()."(".static::getShortClass().")->getOptions()";
		if (! $this->hasOptions()) {
			Debug::error("{$f} options are undefined");
		}
		return $this->options;
	}

	public function setOptions($options)
	{
		return $this->options = $options;
	}

	public function __construct($title = null, $options = null)
	{
		parent::__construct();
		if (! empty($title)) {
			$this->setTitle($title);
		}
		if (! empty($options)) {
			$this->setOptions($options);
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('title', $this->getTitle(), $destroy);
		if ($this->hasOptions()) {
			Json::echoKeyValuePair('options', $this->getOptions(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->options);
		unset($this->title);
	}

	public static function getCommandId(): string
	{
		return "showNotification";
	}
}
