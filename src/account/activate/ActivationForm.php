<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordDatum;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use Exception;

class ActivationForm extends AjaxForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("login_form");
		$this->setIdAttribute("activation_form");
	}

	public function generateFormHeader(): void{
		$this->appendChild(ErrorMessage::getVisualError(INFO_LOGIN_TO_ACTIVATE));
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {

			if (! hasInputParameter('blob_64')) {
				Debug::error("{$f} blob 64 is undefined dammit");
			}

			$inputs = parent::getAdHocInputs();

			$blob_input = new HiddenInput();
			$blob_input->setNameAttribute("blob_64");
			$blob_input->setValueAttribute(getInputParameter('blob_64'));

			$context_input = new HiddenInput();
			$context_input->setNameAttribute("context");
			$context_input->setValueAttribute("first_login");

			$login_input = new HiddenInput();
			$login_input->setNameAttribute("login");
			$login_input->setValueAttribute("login");

			foreach ([
				$blob_input,
				$context_input,
				$login_input
			] as $input) {
				$inputs[$input->getNameAttribute()] = $input;
			}

			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_VALIDATE
		];
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$vn = $input->getColumnName();
			switch ($vn) {
				case NameDatum::getColumnNameStatic():
					$input->setPlaceholderAttribute(_("Username"));
					return SUCCESS;
				case PasswordDatum::getColumnNameStatic():
					$password = _("Password");
					$twelve = substitute(_("%1%+ characters"), 12);
					$placeholder = "{$password} ({$twelve})";
					$password = null;
					$twelve = null;
					$input->setPlaceholderAttribute($placeholder);
					return SUCCESS;
				default:
					return parent::reconfigureInput($input);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array{
		return [
			NameDatum::getColumnNameStatic() => TextInput::class,
			PasswordDatum::getColumnNameStatic() => PasswordInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "activate";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/activate';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_VALIDATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Activate your account");
				$button->setInnerHTML($innerHTML);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
