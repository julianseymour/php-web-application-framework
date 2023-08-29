<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;

class FormDataJavaScriptFunctionsUseCase extends JavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		try {
			if (! app()->hasUseCase()) {
				Debug::error("{$f} application runtime lacks a use case");
			}
			$mode = ALLOCATION_MODE_TEMPLATE;
			foreach (mods()->getFormDataSubmissionClasses() as $form_class) {
				$context_class = $form_class::getTemplateContextClass();
				$context = new $context_class();
				if ($context instanceof TemplateContextInterface) {
					$context->template();
				} else {
					Debug::error("{$f} class \"{$context_class}\" is not a TemplateContextInterface");
				}
				$form = new $form_class($mode);
				$form->bindContext($context);
				echo $form->generateFormDataSubmissionFunction()->toJavaScript()."\n";
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}