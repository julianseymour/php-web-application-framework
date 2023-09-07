<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa\settings;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\MfaSeedDatum;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordDatum;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\input\GhostButton;
use JulianSeymour\PHPWebApplicationFramework\input\GhostInput;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class MfaSettingsForm extends ExpandingMenuNestedForm{

	public function isLocked(): bool{
		$f = __METHOD__;
		if (! $this->hasValidator()) {
			// Debug::print("{$f} form lacks a validator");
			return true;
		}
		$validator = $this->getValidator();
		if($validator->isUninitialized()){
			$did = $validator->getDebugId();
			$decl = $validator->getDeclarationLine();
			Debug::error("{$f} validator is uninitialized. Debug ID is {$did}, declared {$decl}");
		}
		$status = $validator->getObjectStatus();
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} validator has error status \"{$err}\"");
			return true;
		}
		// Debug::print("{$f} form is unlocked");
		return false;
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {
			$inputs = parent::getAdHocInputs();
			$context = $this->getContext();
			if ($this->isLocked() || ! $context->hasMfaSeed()) {
				return $inputs;
			}
			$mfa_status = $context->getMFAStatus();
			switch ($mfa_status) {
				case MFA_STATUS_ENABLED:
				case MFA_STATUS_DISABLED:
					$otp_in1 = new NumberInput();
					$otp_in1->setIdAttribute("mfa-confirm1");
					$otp_in1->setNameAttribute("mfa-confirm1");
					$otp_in1->setLabelString(substitute(_("Verification code %1%"), 1));
					$otp_in1->setWrapperElement(new DivElement());
					$otp_in2 = new NumberInput();
					$otp_in2->setIdAttribute("mfa-confirm2");
					$otp_in2->setNameAttribute("mfa-confirm2");
					$otp_in2->setLabelString(substitute(_("Verification code %1%"), 2));
					$otp_in2->setWrapperElement(new DivElement());
					foreach ([
						$otp_in1,
						$otp_in2
					] as $input) {
						$inputs[$input->getNameAttribute()] = $input;
						$input->setWrapperElement(
							Document::createElement("div")->withStyleProperties([
								"margin-top" => "1rem",
								"position" => "relative"
							])
						);
						$input->configure($this);
					}
					return $inputs;
				default:
					Debug::error("{$f} invalid MFA status \"{$mfa_status}\"");
					return null;
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("mfa_settings_form", "list_leaves", "background_color_5");
		$this->setIdAttribute("mfa_settings_form");
		$this->setStyleProperties([
			"padding" => "1rem"
		]);
	}

	public function getFormInputManifest(): ?array{
		$map1 = [
			'password' => PasswordInput::class
		];
		$map2 = $this->getFormDataIndices();
		return array_merge($map1, $map2);
	}

	public function getDirectives(): ?array{
		$f = __METHOD__;
		try {
			$context = $this->getContext();
			$mfa_status = $context->getMFAStatus();
			if ($context->hasMfaSeed()) {
				if ($this->isLocked()) {
					$map = [
						DIRECTIVE_VALIDATE
					];
					if ($mfa_status === MFA_STATUS_DISABLED) {
						array_push($map, DIRECTIVE_REGENERATE);
						array_push($map, DIRECTIVE_UNSET);
					}
					return $map;
				} elseif ($mfa_status) {
					return [
						DIRECTIVE_UNSET
					];
				} else { // if($mfa_status === MFA_STATUS_DISABLED){
					return [
						DIRECTIVE_UPDATE,
						DIRECTIVE_REGENERATE,
						DIRECTIVE_UNSET
					];
				}
				$gottype = gettype($mfa_status);
				Debug::error("{$f} uh oh, MFA status is \"{$mfa_status}\"; type {$gottype}");
			}
			return [
				DIRECTIVE_REGENERATE
			];
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$input->setWrapperElement(new DivElement());
			$vn = $input->getColumnName();
			switch ($vn) {
				case "password":
					$input->setNameAttribute("mfa-password");
					$placeholder = new ConcatenateCommand(
						_("Password"), 
						" (", 
						substitute(_("%1%+ characters"), 12), 
						")"
					);
					$input->setLabelString($placeholder);
					$input->setWrapperElement(Document::createElement("div")->withStyleProperties([
						"margin-top" => "1rem",
						"position" => "relative"
					]));
					return parent::reconfigureInput($input);
				default:
					return parent::reconfigureInput($input);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateFormHeader(): void{
		$f = __METHOD__;
		try {
			$enter_password = _("Enter your password");
			$context = $this->getContext();
			if ($this->isLocked()) {
				$div = new DivElement();
				if ($context->hasMfaSeed()) {
					$innerHTML = _("Enter your password to reveal QR code and recovery seed"); 
				} else {
					$innerHTML = _("Enter your password to generate a new QR code");
				}
				$div->setInnerHTML($innerHTML);
				$this->appendChild($div);
				return;
			}
			if (! $context->hasMfaSeed()) {
				$innerHTML = _("Enter your password to generate a new QR code");
			} else {
				$mode = $this->getAllocationMode();
				$qr = new MfaQrCodeElement($mode, $context);
				$this->appendChild($qr);
				$mfa_status = $context->getMFAStatus();
				switch ($mfa_status) {
					case MFA_STATUS_DISABLED:
						$innerHTML = _("Enter your password and two consecutive QR codes to enable multifactor authentication");
						break;
					case MFA_STATUS_ENABLED:
						$innerHTML = _("Enter your password and two consecutive QR codes to disable multifactor authentication");
						break;
					default:
						Debug::error("{$f} invalid MFA status \"{$mfa_status}\"");
						return;
				}
			}
			$div = new DivElement();
			$div->setInnerHTML($innerHTML);
			$this->appendChild($div);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getExpandingMenuLabelString($context):string{
		return _("Multifactor authentication");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(): string{
		return "radio_settings_mfa";
	}

	public function getFormDataIndices(): ?array{
		$d = directive();
		$map = [];
		if ($d === DIRECTIVE_REGENERATE || $d === DIRECTIVE_UNSET) {
			$map['MFASeed'] = GhostInput::class;
		}
		if ($d === DIRECTIVE_UNSET || $d === DIRECTIVE_UPDATE) {
			$map['MFAStatus'] = GhostButton::class;
		}
		return $map;
	}

	public function getValidator(): ?Validator
	{
		$f = __METHOD__;
		if($this->hasValidator()){
			return $this->validator;
		}
		$use_case = app()->getUseCase();
		return $this->setValidator(new UpdateMfaSettingsValidator($use_case));
	}

	public static function getMaxHeightRequirement(): string
	{
		return "413px";
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "mfa_settings";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/settings';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		$context = $this->getContext();
		$has_seed = $context->hasMfaSeed();
		$mfa_status = $context->getMFAStatus();
		$button = $this->generateGenericButton($name);
		switch ($name) {
			case DIRECTIVE_REGENERATE:
				if ($this->isLocked() && $has_seed && ! $mfa_status) {
					$innerHTML = _("Generate new QR code");
				} else {
					$innerHTML = _("Generate QR code");
				}
				break;
			case DIRECTIVE_UNSET:
				if($mfa_status){
					$innerHTML = _("Disable MFA");
				}else{
					$innerHTML = _("Destroy QR code");
				}
				$button->setNameAttribute(new ConcatenateCommand($button->getNameAttribute(), "[{$name}]"));
				$button->setValueAttribute(MFA_STATUS_DISABLED);
				break;
			case DIRECTIVE_UPDATE:
				$innerHTML = _("Enable MFA");
				$button->setNameAttribute(new ConcatenateCommand($button->getNameAttribute(), "[{$name}][MFAStatus]"));
				$button->setValueAttribute(MFA_STATUS_ENABLED);
				break;
			case DIRECTIVE_VALIDATE:
				$innerHTML = _("Reveal QR code");
				break;
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
		$button->setInnerHTML($innerHTML);
		$button->setStyleProperties([
			"display" => "block",
			"margin-top" => "1rem"
		]);
		return [
			$button
		];
	}
}
