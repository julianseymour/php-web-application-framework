<?php

namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\account\GetCurrentUserDataCommand;
use JulianSeymour\PHPWebApplicationFramework\account\settings\timezone\GetUserTimezoneCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\BreakCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\PromiseChainCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\TryCatchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\SetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\AlertCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\ErrorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\LogCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\StackTraceCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\CreateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\RemoveAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetStylePropertiesCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\AddEventListenerCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\NegationCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\BlurInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\CheckInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\ClearInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\GetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\IsInputCheckedCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputNameCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SoftDisableInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\ToggleInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\UncheckInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\CreateTextNodeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\DateStringCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\DateTimeStringCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\FloatPrecisionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\StringToLowerCaseCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\StringToUpperCaseCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\TimeStringCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\IsIntegerCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\command\variable\arr\ArrayAccessCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxCommand;

abstract class CommandBuilder{

	public static function addEventListener($eventTarget, $type, $listener): AddEventListenerCommand{
		return new AddEventListenerCommand($eventTarget, $type, $listener);
	}

	public static function closure(...$params): JavaScriptFunction{
		$closure = new JavaScriptFunction(null, ...$params);
		$closure->setArrowFlag(true);
		return $closure;
	}

	public static function constructor($class_name, ...$params):ConstructorCommand{
		return new ConstructorCOmmand($class_name, ...$params);
	}
	
	public static function createTextNode($text): CreateTextNodeCommand{
		return new CreateTextNodeCommand($text);
	}

	public static function getElementById($element): GetElementByIdCommand{
		return new GetElementByIdCommand($element);
	}

	public static function setColumnValue($context, $column_name, $value):SetColumnValueCommand{
		return new SetColumnValueCommand($context, $column_name, $value);
	}
	
	public static function getUserTimezone(): GetUserTimezoneCommand{
		return new GetUserTimezoneCommand();
	}

	public static function getValue($subject): GetInputValueCommand{
		return new GetInputValueCommand($subject);
	}

	public static function if($expr, $then = null, $else = null): IfCommand{
		return new IfCommand($expr, $then, $else);
	}

	public static function isInputChecked($subject): IsInputCheckedCommand{
		return new IsInputCheckedCommand($subject);
	}

	public static function isInteger($val): IsIntegerCommand{
		return new IsIntegerCommand($val);
	}

	public static function promise($expr): PromiseChainCommand{
		return new PromiseChainCommand($expr);
	}

	public static function return($returnValue = null): ReturnCommand{
		return new ReturnCommand($returnValue);
	}

	public static function switch($expr = null, $cases = null, $default = null): SwitchCommand{
		return new SwitchCommand($expr, $cases, $default);
	}

	public static function try(...$blocks): TryCatchCommand{
		return new TryCatchCommand(...$blocks);
	}

	public static function uncheckInput($subject): ?UncheckInputCommand{
		return new UncheckInputCommand($subject);
	}

	public static function alert($msg = null): AlertCommand{
		return new AlertCommand($msg);
	}

	public static function log($msg = null){
		return new LogCommand($msg);
	}

	public static function toggleInput($subject): ToggleInputCommand{
		return new ToggleInputCommand($subject);
	}

	public function trace($msg = null): StackTraceCommand{
		return new StackTraceCommand($msg);
	}

	public static function and(...$params): AndCommand{
		return new AndCommand(...$params);
	}

	public static function or(...$parameters): OrCommand{
		return new OrCommand(...$parameters);
	}

	public static function nop(): NoOpCommand{
		return new NoOpCommand();
	}

	public static function multiply($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::multiply($lhs, $rhs);
	}

	public static function equals($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::equals($lhs, $rhs);
	}

	public static function arrayAccess($array, $offset): ArrayAccessCommand{
		return new ArrayAccessCommand($array, $offset);
	}

	public static function assign($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::assign($lhs, $rhs);
	}

	public static function lessThan($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::lessThan($lhs, $rhs);
	}

	public static function lessThanOrEquals($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::lessThanOrEquals($lhs, $rhs);
	}

	public static function greaterThan($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::greaterThan($lhs, $rhs);
	}

	public static function greaterThanOrEquals($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::greaterThanOrEquals($lhs, $rhs);
	}

	public static function notEquals($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::notEquals($lhs, $rhs);
	}

	public static function add($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::add($lhs, $rhs);
	}

	public static function subtract($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::subtract($lhs, $rhs);
	}

	public static function divide($lhs, $rhs): BinaryExpressionCommand{
		return BinaryExpressionCommand::divide($lhs, $rhs);
	}

	public static function blur($subject): BlurInputCommand{
		return new BlurInputCommand($subject);
	}

	public static function break(): BreakCommand{
		return new BreakCommand();
	}

	public static function concatenate($s1, ...$more): ConcatenateCommand{
		return new ConcatenateCommand($s1, ...$more);
	}

	public static function construct($objectClass, ...$params): ConstructorCommand{
		return new ConstructorCommand($objectClass, ...$params);
	}

	public static function createElement(string $tag, ?string $type = null, int $mode = ALLOCATION_MODE_UNDEFINED){
		return new CreateElementCommand($tag, $type, $mode);
	}

	public static function call($name, ...$params): CallFunctionCommand{
		return new CallFunctionCommand($name, ...$params);
	}

	public static function checkInput($subject): CheckInputCommand{
		return new CheckInputCommand($subject);
	}

	public static function clearInput($subject): ClearInputCommand{
		return new ClearInputCommand($subject);
	}

	public static function dateTimeString($subject, $timezone = null, ?string $format = null){
		return new DateTimeStringCommand($subject, $timezone, $format);
	}

	public static function floatPrecision($subject, $precision): FloatPrecisionCommand{
		return new FloatPrecisionCommand($subject, $precision);
	}

	public static function getDeclaredVariable($vn, ?Scope $scope = null, ?string $parseType = null): GetDeclaredVariableCommand{
		return new GetDeclaredVariableCommand($vn, $scope, $parseType);
	}

	public static function negate($expression): NegationCommand{
		return new NegationCommand($expression);
	}

	public static function setAttribute($element, $attributes): SetAttributeCommand{
		return new SetAttributeCommand($element, $attributes);
	}

	public static function setInnerHTML($id, $innerHTML): SetInnerHTMLCommand{
		return new SetInnerHTMLCommand($id, $innerHTML);
	}

	public static function setName($subject, $name): SetInputNameCommand{
		return new SetInputNameCommand($subject, $name);
	}

	public static function setValue($subject, $value): SetInputValueCommand{
		return new SetInputValueCommand($subject, $value);
	}

	public static function softDisable($subject): SoftDisableInputCommand{
		return new SoftDisableInputCommand($subject);
	}

	public static function stringToUpperCase($subject): StringToUpperCaseCommand{
		return new StringToUpperCaseCommand($subject);
	}

	public static function stringToLowerCase($subject): StringToLowerCaseCommand{
		return new StringToLowerCaseCommand($subject);
	}

	public static function error($msg): ErrorCommand{
		return new ErrorCommand($msg);
	}

	public static function infoBox($content): InfoBoxCommand{
		return new InfoBoxCommand($content);
	}

	public static function let(string $name, $value = null): DeclareVariableCommand{
		return DeclareVariableCommand::let($name, $value);
	}

	public static function var(string $name, $value = null): DeclareVariableCommand{
		return DeclareVariableCommand::var($name, $value);
	}

	public static function const(string $name, $value = null): DeclareVariableCommand{
		return DeclareVariableCommand::const($name, $value);
	}

	public static function setStyleProperties($element, $properties): SetStylePropertiesCommand{
		return new SetStylePropertiesCommand($element, $properties);
	}

	public static function updateElement(...$elements): UpdateElementCommand{
		return new UpdateElementCommand(...$elements);
	}

	public static function user(): GetCurrentUserDataCommand{
		return new GetCurrentUserDataCommand();
	}

	public static function removeAttribute($element, $attributes): RemoveAttributeCommand{
		return new RemoveAttributeCommand($element, $attributes);
	}

	public static function getColumnValue($context, string $column_name): GetColumnValueCommand{
		return new GetColumnValueCommand($context, $column_name);
	}

	public static function hasColumnValue($context, string $column_name): HasColumnValueCommand{
		return new HasColumnValueCommand($context, $column_name);
	}
	
	public static function dateString($subject, $timezone=null, ?string $format=null):DateStringCommand{
		return new DateStringCommand($subject, $timezone, $format);
	}
	
	public static function timeString($subject, $timezone=null, ?string $format=null):TimeStringCommand{
		return new TimeStringCommand($subject, $timezone, $format);
	}
}
