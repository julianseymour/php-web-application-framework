<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use JulianSeymour\PHPWebApplicationFramework\validate\InstantValidatorInterface;

/**
 * Counterpart to the ServerConfirmPasswordValidator.
 * Its role is fulfilled in ClientConfirmPasswordValidator.js
 *
 * @author j
 */
class ClientConfirmPasswordValidator extends ConfirmPasswordValidator implements InstantValidatorInterface
{

	public static function getJavaScriptClassPath(): ?string
	{
		$fn = get_class_filename(static::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public function setCounterpartNameAttribute($counterpartName)
	{
		if ($this->hasInput()) {
			$this->getInput()->setAttribute("__match", $counterpartName);
		}
		return parent::setCounterpartNameAttribute($counterpartName);
	}

	public function setInput($input)
	{
		if (! $input->hasAttribute("__match") && $this->hasCounterpartNameAttribute()) {
			$input->setAttribute("__match", $this->getCounterpartNameAttribute());
		}
		return parent::setInput($input);
	}
}
