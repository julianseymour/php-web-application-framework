<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\event\DispatchEventCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\BlurInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\CheckInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\ClearInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\FocusInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\GetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\IsInputCheckedCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputNameCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SoftDisableInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\ToggleInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\UncheckInputCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetElementByIdCommand extends ElementCommand implements ValueReturningCommandInterface{

	public static function getCommandId(): string{
		return "getElementById";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$print = false;
		$id = $this->getId();
		// XXX before JavaScriptCommmandInterface
		if($id instanceof JavaScriptInterface){
			$id = $id->toJavaScript();
		}elseif(is_string($id) || $id instanceof StringifiableInterface){
			$id = single_quote($id);
		}
		if($print){
			Debug::print("{$f} ID attribute is \"{$id}\"");
		}
		return "document.getElementById({$id})";
	}

	public function evaluate(?array $params = null){
		return $this->getElement();
	}

	public function addClass($class): AddClassCommand{
		return new AddClassCommand($this, $class);
	}

	public function appendChild(...$children): AppendChildCommand{
		return new AppendChildCommand($this, ...$children);
	}

	public function fade(): FadeElementCommand{
		return new FadeElementCommand($this);
	}

	public function getInnerHTML(): GetInnerHTMLCommand{
		return new GetInnerHTMLCommand($this);
	}

	public function getOffsetHeight(): GetOffsetHeightCommand{
		return new GetOffsetHeightCommand($this);
	}

	public function getOffsetWidth(): GetOffsetWidthCommand{
		return new GetOffsetWidthCommand($this);
	}

	public function getParentNode(): GetParentNodeCommand{
		return new GetParentNodeCommand($this);
	}

	public function insertAfter(...$elements): InsertAfterCommand{
		return new InsertAfterCommand($this, ...$elements);
	}

	public function insertBefore(...$elements): InsertBeforeCommand{
		return new InsertBeforeCommand($this, ...$elements);
	}

	public function removeAttribute(...$attr_names): RemoveAttributeCommand{
		return new RemoveAttributeCommand($this, ...$attr_names);
	}

	public function scrollIntoView($align): ScrollIntoViewCommand{
		return new ScrollIntoViewCommand($this, $align);
	}

	public function setAttribute($attributes): SetAttributeCommand{
		return new SetAttributeCommand($this, $attributes);
	}

	public function setClassName($className): SetClassNameCommand{
		return new SetClassNameCommand($this, $className);
	}

	public function setInnerHTML($innerHTML): SetInnerHTMLCommand{
		return new SetInnerHTMLCommand($this, $innerHTML);
	}

	public function setHTMLFor($for): SetLabelHTMLForCommand{
		return new SetLabelHTMLForCommand($this, $for);
	}

	public function setStyleProperties($properties): SetStylePropertiesCommand{
		return new SetStylePropertiesCommand($this, $properties);
	}

	public function setTextContent($content): SetTextContentCommand{
		return new SetTextContentCommand($this, $content);
	}

	public function blur(): BlurInputCommand{
		return new BlurInputCommand($this);
	}

	public function check(): CheckInputCommand{
		return new CheckInputCommand($this);
	}

	public function focus(): FocusInputCommand{
		return new FocusInputCommand($this);
	}

	public function getValue(): GetInputValueCommand{
		return new GetInputValueCommand($this);
	}

	public function isChecked(): IsInputCheckedCommand{
		return new IsInputCheckedCommand($this);
	}

	public function clear(): ClearInputCommand{
		return new ClearInputCommand($this);
	}

	public function setName($name): SetInputNameCommand{
		return new SetInputNameCommand($this, $name);
	}

	public function setValue($value): SetInputValueCommand{
		return new SetInputValueCommand($this, $value);
	}

	public function disable(): SoftDisableInputCommand{
		return new SoftDisableInputCommand($this);
	}

	public function toggle(): ToggleInputCommand{
		return new ToggleInputCommand($this);
	}

	public function uncheck(): UncheckInputCommand{
		return new UncheckInputCommand($this);
	}

	public function dispatchEventCommand($event, ...$parameters):DispatchEventCommand{
		return new DispatchEventCommand($this, $event, ...$parameters);
	}
}
