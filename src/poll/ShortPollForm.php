<?php

namespace JulianSeymour\PHPWebApplicationFramework\poll;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;
use Exception;

class ShortPollForm extends AjaxForm{

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("notify_form");
		$this->hide();
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try{
			$inputs = parent::getAdHocInputs();
			$mode = $this->getAllocationMode();
			$ii = new NumberInput($mode);
			$ii->setNameAttribute("update_interval");
			$ii->setValueAttribute(30000);
			$ii->setIdAttribute("update_interval");

			$swi = new HiddenInput($mode);
			$swi->setNameAttribute("sw");
			$swi->setValueAttribute(0);
			$swi->setIdAttribute("use_service_worker");

			$dirty_bit = new HiddenInput($mode);
			$dirty_bit->setValueAttribute("");
			$dirty_bit->setIdAttribute("messenger_dirty_bit");

			$push_sub = new HiddenInput($mode);
			$push_sub->setIdAttribute("pushSubscriptionKey");
			$push_sub->setValueAttribute("");

			foreach([
				$ii,
				$swi
			] as $input){
				$inputs[$input->getNameAttribute()] = $input;
			}
			foreach([
				$push_sub,
				$dirty_bit
			] as $input){
				$inputs[$input->getIdAttribute()] = $input;
			}

			return $inputs;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$vn = $input->getColumnName();
			switch($vn){
				case "notificationDeliveryTimestamp":
					$input->setIdAttribute("notify_ts");
					return SUCCESS;
				case 'uniqueKey':
					$input->setIdAttribute("user_key");
					return SUCCESS;
				case "subtype":
					$input->setIdAttribute("account_type");
					return SUCCESS;
				default:
					return parent::reconfigureInput($input);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array{
		return [
			'notificationDeliveryTimestamp' => HiddenInput::class,
			"subtype" => HiddenInput::class
			// 'uniqueKey' => HiddenInput::class
		];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_READ_MULTIPLE
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "poll";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/poll';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_READ_MULTIPLE:
				$button = new ButtonInput();
				$button->setNameAttribute("directive");
				$button->setValueAttribute($name);
				$innerHTML = _("Check for updates");
				$button->setInnerHTML($innerHTML);
				$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
				$button->setTypeAttribute("submit");
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public static function getIdAttributeStatic(): ?string{
		return "notify_form";
	}
}
