<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\change;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordGeneratingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordGeneratingFormTrait;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordValidator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class ChangePasswordForm extends ExpandingMenuNestedForm implements PasswordGeneratingFormInterface
{

	use PasswordGeneratingFormTrait;

	public function getDirectives(): ?array
	{
		return [
			DIRECTIVE_REGENERATE
		];
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("list_leaves", "background_color_5", "text-align_center");
		$this->setStyleProperties(["padding" => "1rem"]);
		$this->setIdAttribute("change_password_form");
	}

	public function getValidateInputNames(): ?array{
		$f = __METHOD__;
		$print = false;
		$names = parent::getValidateInputNames();
		array_push($names, "password_new");
		if ($print) {
			Debug::print("{$f} returning the following indices");
			Debug::printArray($names);
		}
		return $names;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$vn = $input->getColumnName();
			switch ($vn) {
				case "password":
					$input->setLabelString(_("Current password"));
					$input->setWrapperElement(Document::createElement("div")->withStyleProperties([
						"position" => "relative"
					]));
					$input->setRequiredAttribute("required");
					break;
				default:
			}
			return parent::reconfigureInput($input);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {
			$mode = $this->getAllocationMode();
			$inputs = parent::getAdHocInputs();
			$npi = new PasswordInput($mode);
			$npi->setColumnName($npi->setNameAttribute("password_new"));
			$npi->setForm($this);
			$npi->setRequiredAttribute("required");
			$new_password = _("New password");
			$twelve_plus = substitute(_("%1%+ characters"), 12);
			$placeholder = "{$new_password} ({$twelve_plus})";
			$npi->setLabelString($placeholder);
			$npi->setWrapperElement(new DivElement($mode));
			$npi->setWrapperElement(Document::createElement("div")->withStyleProperties([
				"margin-bottom" => "1rem",
				"margin-top" => "1rem",
				"position" => "relative"
			]));
			$npi->configure($this);
			$suffix = new DivElement($mode);
			$suffix->addClassAttribute("js_valid_light");
			$suffix->setAllowEmptyInnerHTML(true);
			$npi->pushSuccessor($suffix);
			$inputs[$npi->getNameAttribute()] = $npi;
			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function attachInputValidators(InputInterface $input): InputInterface
	{
		$f = __METHOD__; //ChangePasswordForm::getShortClass()."(".static::getShortClass().")->attachInputValidators()";
		try {
			$print = false;
			$cn = $input->getColumnName();
			switch ($cn) {
				case "password":
					$input->pushValidator(new PasswordValidator("password"));
					break;
				case "password_new":
					if ($print) {
						Debug::print("{$f} attaching input validators to new password input");
					}
					$input->setMinimumLengthAttribute(12);
					$input->pushValidator($this->getConfirmPasswordValidator());
					break;
				default:
			}
			return parent::attachInputValidators($input);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getValidator(): ?Validator
	{
		if($this->hasValidator()){
			return $this->validator;
		}
		$validator = new FormDataIndexValidator($this);
		$validator->setCovalidateWhen(COVALIDATE_AFTER);
		return $this->setValidator($validator);
		// return new ChangePasswordValidator($this);
	}

	public static function getExpandingMenuLabelString($context): string
	{
		return _("Change password");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(): string
	{
		return "radio_settings_password";
	}

	public function getFormDataIndices(): ?array
	{
		return [
			"password" => PasswordInput::class
		];
	}

	public static function getMaxHeightRequirement(): string
	{
		return "213px";
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "change_password";
	}

	public static function getActionAttributeStatic(): ?string
	{
		return '/settings';
	}

	public function generateButtons(string $name): ?array
	{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_REGENERATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Change password");
				$button->setInnerHTML($innerHTML);
				$button->setStyleProperties([
					"margin-top" => "1rem"
				]);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public static function getPasswordInputName(): string
	{
		return "password_new";
	}

	public static function getConfirmPasswordInputName(): string
	{
		return "confirm_new";
	}
}
