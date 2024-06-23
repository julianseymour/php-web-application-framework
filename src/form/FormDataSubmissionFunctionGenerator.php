<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
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

class FormDataSubmissionFunctionGenerator extends JavaScriptFunctionGenerator{

	public function generate($form): ?JavaScriptFunction{
		$f = __METHOD__;
		try{
			$print = false;
			$class = get_short_class($form);
			$func = new JavaScriptFunction("submit{$class}", "context");
			$func->setRoutineType(ROUTINE_TYPE_FUNCTION);
			// $func->setTemplateFlag(true);
			$func->pushCodeBlock(new LogCommand("context"), new StackTraceCommand());
			$fd = DeclareVariableCommand::let("fd", new ConstructorCommand("FormData"));
			$fd->setEscapeType(null);
			$func->pushCodeBlock($fd);
			if($print){
				Debug::print("{$f} about to iterate over inputs from ".$form->getDebugString());
			}
			foreach($form->getInputs() as $input){
				$print = $print || $input->getDebugFlag();
				if(!$input->hasColumnName()){
					if($print){
						Debug::print("{$f} input ".$input->getDebugString()." does not have a column name");
					}
					//deallocate($input);
					continue;
				}elseif($print){
					Debug::print("{$f} about to get form data appension command for ".$input->getDebugString());
				}
				$fdac = $input->getFormDataAppensionCommand("fd");
				$func->pushCodeBlock($fdac->toJavaScript());
				$input->disableDeallocation();
				deallocate($fdac);
				$input->enableDeallocation();
			}
			if($print){
				Debug::print("{$f} on the far side of the foreach loop");
			}
			$method = $form->getMethodAttribute();
			$action = $form->getActionAttribute();
			if(!$form->skipAntiXsrfTokenInputs()){
				if($print){
					Debug::print("{$f} we are not skipping anti-XSRF token inputs");
				}
				$mode = ALLOCATION_MODE_TEMPLATE;
				$xsrf_token = $form->getAntiXsrfTokenInput($mode);
				$append_xsrf_token = new CallFunctionCommand("fd.append", "xsrf_token", $xsrf_token->getValueAttribute());
				$secondary_hmac = $form->getSecondaryHmacInput($mode, $form->getActionAttribute());
				$append_hmac = new CallFunctionCommand("fd.append", "secondary_hmac", $secondary_hmac->getValueAttribute());
				$func->pushCodeBlock(
					$append_xsrf_token->toJavaScript(), 
					$append_hmac->toJavaScript()
				);
				deallocate($append_xsrf_token);
				deallocate($xsrf_token);
				deallocate($append_hmac);
				deallocate($secondary_hmac);
			}elseif($print){
				Debug::print("{$f} skipping anti-XSRF token inputs");
			}
			$callback_success = new GetDeclaredVariableCommand($form->getSuccessCallback());
			$callback_error = new GetDeclaredVariableCommand($form->getErrorCallback());
			$fd = new GetDeclaredVariableCommand("fd");
			$func->pushCodeBlock(
				IfCommand::if(
					new CallFunctionCommand("isWebWorker")
				)->then(
					new CallFunctionCommand("fetch_client", $action, $fd, $callback_success, $callback_error)
				)->else(
					new CallFunctionCommand("fetch_xhr", $method, $action, $fd, $callback_success, $callback_error)
				)
			);
			if($print){
				Debug::print("{$f} returning \"{$func}\"");
			}
			return $func;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
