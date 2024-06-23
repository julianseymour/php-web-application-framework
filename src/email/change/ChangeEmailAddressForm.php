<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use JulianSeymour\PHPWebApplicationFramework\account\register\RegistrationEmailAddressValidator;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeGeneratingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\input\EmailInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;
use JulianSeymour\PHPWebApplicationFramework\validate\DatumValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

class ChangeEmailAddressForm extends ExpandingMenuNestedForm implements ConfirmationCodeGeneratingFormInterface{

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_EMAIL_CONFIRMATION_CODE
		];
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("list_leaves", "background_color_5", "sl");
		$this->setIdAttribute("change_email_form");
		$this->setStyleProperties(["padding" => "1rem"]);
	}

	public static function getMaxHeightRequirement(): string{
		return "91px";
	}

	public static function getExpandingMenuLabelString($context){
		return _("Change email address");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(): string{
		return "radio_settings_change_email";
	}

	public function getFormDataIndices(): ?array{
		return [
			"emailAddress" => EmailInput::class
			// 'gpgPublicKey' => PasswordInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "change_email";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/settings';
	}

	public function getConfirmationCodeClass(): string{
		return ChangeEmailAddressConfirmationCode::class;
	}

	public function getValidator(): ?Validator{
		if($this->hasValidator()){
			return $this->validator;
		}
		$validator = new AntiXsrfTokenValidator($this->getActionAttribute());
		// app()->getUseCase(), $this->getActionAttribute());
		$value = null;
		if(hasInputParameter("emailAddress")){
			$value = getInputParameter("emailAddress");
		}
		$validator->setCovalidateWhen(CONST_AFTER);
		$validator->pushCovalidators(new DatumValidator(new EmailAddressDatum("emailAddress"), $value));
		return $this->setValidator($validator);
	}

	protected function attachInputValidators(InputlikeInterface $input): InputlikeInterface{
		if(!$input->hasColumnName()){
			return $input;
		}
		$cn = $input->getColumnName();
		switch($cn){
			case "emailAddress":
				$input->pushValidator(new RegistrationEmailAddressValidator());
				break;
			default:
		}
		return parent::attachInputValidators($input);
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_EMAIL_CONFIRMATION_CODE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Save email address");
				$button->setInnerHTML($innerHTML);
				$button->setStyleProperties(["margin-top" => "1rem"]);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public function reconfigureInput($input): int{
		$cn = $input->getColumnName();
		switch($cn){
			case "emailAddress":
				$input->setIdAttribute("change_email_address_input");
				$input->setLabelString(_("Enter email address"));
				$input->setWrapperElement(Document::createElement("div")->withStyleProperties([
					"position" => "relative"
				]));
				$input->setRequiredAttribute("required");
				break;
			default:
				break;
		}
		return parent::reconfigureInput($input);
	}

	// name="gpgkey" placeholder="GPG public key (optional)"
}
