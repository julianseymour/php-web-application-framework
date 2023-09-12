<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\NegationCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class HasColumnValueCommand extends ColumnValueCommand{

	public static function getCommandId(): string{
		return "hasColumnValue";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			$context = $this->getDataStructure();
			while ($context instanceof ValueReturningCommandInterface) {
				$context = $context->evaluate();
			}
			$vn = $this->getColumnName();
			while ($vn instanceof ValueReturningCommandInterface) {
				$vn = $vn->evaluate();
			}
			if($print) {
				$key = $context->getIdentifierValue();
				$sc = get_short_class($context);
				$did = $context->getDebugId();
				$decl = $context->getDeclarationLine();
				if($context->hasColumnValue($vn)) {
					$value = $context->getColumnValue($vn);
					Debug::print("{$f} yes, context of class \"{$sc}\" with key \"{$key}\" has a value for column \"{$vn}\", and its \"{$value}\"");
				}else{
					Debug::print("{$f} no, context of class \"{$sc}\" with key \"{$key}\" and debig ID {$did} instantiated {$decl} does not have a value for column \"{$vn}\"");
				}
			}
			return $context->hasColumnValue($vn);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getConditionalString(){
		return $this->__toString();
	}

	public function negate(): NegationCommand{
		return CommandBuilder::negate($this);
	}
}
