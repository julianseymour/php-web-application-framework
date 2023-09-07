<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\activate;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\use_case;
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
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;

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
			$request = request();
			$use_case = use_case();
			if (! $request->hasInputParameter('blob_64', $use_case)) {
				Debug::error("{$f} blob 64 is undefined dammit");
			}

			$inputs = parent::getAdHocInputs();

			$blob_input = new HiddenInput();
			$blob_input->setNameAttribute("blob_64");
			$blob_input->setValueAttribute($request->getInputParameter('blob_64', $use_case));

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
			DIRECTIVE_LOGIN
		];
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$vn = $input->getColumnName();
			switch ($vn) {
				case 'name':
					$input->setLabelString(_("Username"));
					break;
				case 'password':
					$password = _("Password");
					$twelve = substitute(_("%1%+ characters"), 12);
					$placeholder = "{$password} ({$twelve})";
					$password = null;
					$twelve = null;
					$input->setLabelString($placeholder);
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
			'name' => TextInput::class,
			'password' => PasswordInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "activate";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/activate';
	}

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		switch ($directive) {
			case DIRECTIVE_LOGIN:
				$button = new ButtonInput();
				$button->setNameAttribute("directive");
				$button->setValueAttribute(DIRECTIVE_LOGIN);
				$form_id = $this->getIdAttribute();
				$button->setIdAttribute("{$directive}-{$form_id}");
				$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
				$button->setTypeAttribute("submit");
				$button->setForm($this);
				$innerHTML = _("Activate your account");
				$button->setInnerHTML($innerHTML);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid directive \"{$directive}\"");
				return null;
		}
	}
}
