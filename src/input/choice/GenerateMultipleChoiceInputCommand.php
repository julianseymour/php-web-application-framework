<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;

class GenerateMultipleChoiceInputCommand extends ElementCommand 
implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;
	use ChoiceGeneratorTrait;
	use ParametricTrait;
	
	public function __construct($element, $generator, ...$params){
		parent::__construct($element);
		if ($generator !== null) {
			$this->setChoiceGenerator($generator);
		}
		if (isset($params)) {
			$this->setParameters($params);
		}
	}

	public static function getCommandId(): string{
		return "generateMultipleChoiceInput";
	}

	public function extractChildNodes(int $mode): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function extractAnyway(){
		return false;
	}

	public function incrementVariableName(int &$counter){
		$f = __METHOD__;
		if (! $this->hasElement()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} element is undefined. Declared {$decl}");
		}
		return $this->getElement()->incrementVariableName($counter);
	}

	public function evaluate(?array $params = null){
		return $this->extractChildNodes($this->getAllocationMode());
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->hasChoiceGenerator()) {
				Debug::error("{$f} choice generator is undefined");
			}
			$e = $this->getElement();
			if ($print) {
				$ec = $e->getClass();
				$did = $e->getDebugId();
				$decl = $e->getDeclarationLine();
				Debug::print("{$f} element is a {$ec} with debug ID {$did}, declared {$decl}");
			}
			$cg = $this->getChoiceGenerator();
			if (! $cg->hasGeneratedFunction()) {
				$cg->setGeneratedFunction($cg->generate(null));
			}
			$gf = $cg->getGeneratedFunction();
			$params = $cg->getClientSideChoiceGenerationParameters($e);
			if ($e instanceof SelectInput) {
				$fn = "generateSelectOptions";
			} elseif ($e instanceof MultipleChoiceInput) {
				$ec2 = $e->getElementClass();
				switch ($ec2) {
					case OptionElement::class:
						$fn = "generateSelectOptions";
						$e = $e->getParentNode();
						break;
					case RadioButtonInput::class:
						$fn = "generateRadioButtons";
						break;
					case CheckboxInput::class:
						$fn = "generateCheckboxes";
						break;
					default:
						Debug::error("{$f} unsupported multiple choice element class \"{$ec2}\"");
				}
			}
			$cf = new CallFunctionCommand($fn, $e, new CallFunctionCommand($gf->getName(), ...$params));
			return $cf->toJavaScript();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
