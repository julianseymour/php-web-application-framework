<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;
use JulianSeymour\PHPWebApplicationFramework\input\StringInput;

class NaturalInputValidator extends Validator implements InstantValidatorInterface
{

	public function __construct(InputInterface $input)
	{
		parent::__construct();
		$this->setInput($input);
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //NaturalInputValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		$input = $this->getInput();
		if($print) {
			$name = $input->getNameAttribute();
			Debug::print("{$f} about to validate input \"{$name}\"");
		}
		if($input->hasAttribute("required") && empty($validate_me)) {
			if($print) {
				Debug::print("{$f} required value is missing");
			}
			return FAILURE;
		}elseif($input instanceof StringInput) {
			if($input->hasMinimumLengthAttribute() && strlen($validate_me) < $input->getMinimumLength()) {
				if($print) {
					Debug::warning("{$f} under minimum string length");
				}
				return ERROR_MINIMUM_LENGTH;
			}elseif($input->hasMaximumLengthAttribute() && strlen($validate_me) > $input->getMaximumLength()) {
				if($print) {
					Debug::warning("{$f} maximum string length exceeded");
				}
				return ERROR_MAXIMUM_LENGTH;
			}elseif($input->hasPatternAttribute() && ! preg_match($input->getPatternAttribute(), $validate_me)) {
				if($print) {
					Debug::warning("{$f} pattern mismatch");
				}
				return ERROR_PATTERN_MISMATCH;
			}
		}elseif($input instanceof NumberInput) {
			if(!is_int($validate_me) && ! is_float($validate_me) && ! is_double($validate_me)) {
				if($print) {
					Debug::warning("{$f} invalid input type");
				}
				return ERROR_TYPE;
			}elseif($input->hasMinimumAttribute() && $validate_me < $input->getMinimumAttribute()) {
				if($print) {
					Debug::warning("{$f} below minimum value");
				}
				return ERROR_MINIMUM_VALUE;
			}elseif($input->hasMaximumAttribute() && $validate_me > $input->getMaximumAttribute()) {
				if($print) {
					Debug::warning("{$f} maximum value exceeded");
				}
				return ERROR_MAXIMUM_VALUE;
			}
		}elseif($print) {
			Debug::print("{$f} input is something other than string or numeric");
		}
		if($print) {
			Debug::print("{$f} validation successful");
		}
		return SUCCESS;
	}
}
