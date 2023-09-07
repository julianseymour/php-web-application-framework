<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeForm;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordGeneratingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordGeneratingFormTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use JulianSeymour\PHPWebApplicationFramework\validate\ClosureValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class ResetPasswordForm extends ConfirmationCodeForm implements PasswordGeneratingFormInterface{

	use PasswordGeneratingFormTrait;

	protected function attachInputValidators(InputInterface $input): InputInterface{
		$f = __METHOD__;
		if(!$input->hasColumnName()){
			return $input;
		}
		$cn = $input->getColumnName();
		switch ($cn) {
			case "name":
				$closure = function (array &$validate_me): int {
					$f = "ResetPasswordForm.attachInputValidators()";
					try {
						$print = false;
						if (! array_key_exists('name', $validate_me)) {
							Debug::warning("{$f} name was not posted");
							Debug::printArray($validate_me);
							Debug::printStackTrace();
						} elseif ($print) {
							Debug::print("{$f} entered");
							Debug::printArray($validate_me);
						}
						$normalized = NameDatum::normalize($validate_me['name']);
						$correspondent = user()->getCorrespondentObject();
						$real_name = $correspondent->getNormalizedName();
						if ($normalized === $real_name) {
							if ($print) {
								Debug::print("{$f} normalized names \"{$normalized}\" match");
							}
							return SUCCESS;
						} elseif ($print) {
							Debug::print("{$f} normalized name from post \"{$normalized}\" does not match user's actual name \"{$real_name}\"");
						}
						return ERROR_LOGIN_CREDENTIALS;
					} catch (Exception $x) {
						x($f, $x);
					}
				};
				$input->pushValidator(new ClosureValidator($closure));
				break;
			case "password":
				$input->pushValidator($this->getConfirmPasswordValidator());
				break;
			default:
		}
		return parent::attachInputValidators($input);
	}

	public function getValidator(): ?Validator{
		if($this->hasValidator()){
			return $this->validator;
		}
		$validator = new FormDataIndexValidator($this);
		$validator->setCovalidateWhen(COVALIDATE_AFTER);
		return $this->setValidator($validator);
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("reset_password_form");
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_VALIDATE
		];
	}

	public function generateFormHeader(): void{
		$div = new DivElement();
		$div->setInnerHTML(_("Enter your account information to continue"));
		$this->appendChild($div);
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$vn = $input->getColumnName();
			switch ($vn) {
				case "name":
					$input->setLabelString(_("Username"));
					$input->setMinimumLengthAttribute(1);
					break;
				case "password":
					$input->setAutocompleteAttribute("off");
					$input->setRequiredAttribute("required");
					$newpass = _("New password");
					$chars = substitute(_("%1%+ characters"), 12);
					$input->setLabelString("{$newpass} ({$chars})");
					break;
				default:
			}
			return parent::reconfigureInput($input);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array{
		return [
			"name" => TextInput::class,
			"password" => PasswordInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "reset_password";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/reset';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_VALIDATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Reset password");
				$button->setInnerHTML($innerHTML);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public static function getPasswordInputName(){
		return "password";
	}

	public static function getConfirmPasswordInputName(){
		return "confirm";
	}
}
