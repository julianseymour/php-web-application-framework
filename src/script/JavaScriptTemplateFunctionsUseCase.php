<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;

class JavaScriptTemplateFunctionsUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		try{
			$print = false;
			$mode = ALLOCATION_MODE_TEMPLATE;
			foreach(mods()->getTemplateElementClasses() as $tec){
				if(!class_exists($tec)){
					Debug::error("{$f} element class \"{$tec}\" does not exist");
				}
				$context_class = $tec::getTemplateContextClass();
				if(!class_exists($context_class)){
					Debug::error("{$f} class \"{$context_class}\" does not exist");
				}elseif($print){
					Debug::print("{$f} template context class ".get_short_class($context_class));
				}
				$context = new $context_class();
				if($print){
					Debug::print("{$f} instantiated ".$context->getDebugString());
				}
				$context->disableDeallocation();
				if($context instanceof TemplateContextInterface){
					$context->template();
				}elseif($print){
					Debug::print("{$f} class \"{$context_class}\" is not a TemplateContextInterface");
				}
				$element = new $tec($mode);
				$element->disableDeallocation();
				$element->bindContext($context);
				$function = $element->generateTemplateFunction();
				$function->disableDeallocation();
				echo $function->toJavaScript();
				$function->enableDeallocation();
				deallocate($function);
				$element->enableDeallocation();
				deallocate($element);
				$context->enableDeallocation();
				deallocate($context);
			}
			//bind element functions constant
			$const = DeclareVariableCommand::const("bindElementFunctions");
			$const->setEscapeType(ESCAPE_TYPE_OBJECT);
			$arr = [];
			foreach(mods()->getTemplateElementClasses() as $class){
				if($print){
					Debug::print("{$f} template element class \"{$class}\"");
				}
				$short = get_short_class($class);
				$arr[$short] = new GetDeclaredVariableCommand("bind{$short}");
			}
			$const->setValue($arr);
			echo $const->toJavaScript()."\n";
			deallocate($const);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function getFilename(): string{
		return "template.js";
	}
}
