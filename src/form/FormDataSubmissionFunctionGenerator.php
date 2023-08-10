<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\LogCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\StackTraceCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;
use Exception;

class FormDataSubmissionFunctionGenerator extends JavaScriptFunctionGenerator
{

	public function generate($form): ?JavaScriptFunction
	{
		$f = __METHOD__; //FormDataSubmissionFunctionGenerator::getShortClass()."(".static::getShortClass().")::generate()";
		try {
			$print = false;
			$class = get_short_class($form);
			$func = new JavaScriptFunction("submit{$class}", "context");
			$func->setRoutineType(ROUTINE_TYPE_FUNCTION);
			// $func->setTemplateFlag(true);
			$func->pushSubcommand(new LogCommand("context"), new StackTraceCommand());
			$fd = DeclareVariableCommand::let("fd", new ConstructorCommand("FormData"));
			$fd->setEscapeType(null);
			$func->pushSubcommand($fd);
			$inputs = $form->getInputs();
			foreach ($inputs as $input) {
				if (! $input->hasColumnName()) {
					continue;
				}
				$func->pushSubcommand($input->getFormDataAppensionCommand("fd")
					->toJavaScript());
			}
			$method = $form->getMethodAttribute();
			$action = $form->getActionAttribute();
			if (! $form->skipAntiXsrfTokenInputs()) {
				$mode = ALLOCATION_MODE_TEMPLATE;
				$xsrf_token = $form->getAntiXsrfTokenInput($mode);
				$secondary_hmac = $form->getSecondaryHmacInput($mode, $form->getActionAttribute());
				$func->pushSubcommand((new CallFunctionCommand("fd.append", "xsrf_token", $xsrf_token->getValueAttribute()))->toJavaScript(), (new CallFunctionCommand("fd.append", "secondary_hmac", $secondary_hmac->getValueAttribute()))->toJavaScript());
			}
			$callback_success = new GetDeclaredVariableCommand($form->getSuccessCallback());
			$callback_error = new GetDeclaredVariableCommand($form->getErrorCallback());
			$fd = new GetDeclaredVariableCommand("fd");
			$func->pushSubcommand(IfCommand::if(new CallFunctionCommand("isWebWorker"))->then(new CallFunctionCommand("fetch_client", $action, $fd, $callback_success, $callback_error))
				->else(new CallFunctionCommand("fetch_xhr", $method, $action, $fd, $callback_success, $callback_error)));
			if ($print) {
				Debug::print("{$f} returning \"{$func}\"");
			}
			return $func;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}