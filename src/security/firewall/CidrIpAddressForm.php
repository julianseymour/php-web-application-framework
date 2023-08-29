<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\GhostButton;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use Exception;

class CidrIpAddressForm extends AjaxForm
{

	public function bindContext($context){
		$ret = parent::bindContext($context);
		$key = $this->getResolvedKey($context);
		$id = new ConcatenateCommand("cidr_ip_form-", $key);
		$this->setIdAttribute($id);
		return $ret;
	}

	public static function getMethodAttributeStatic():?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$vn = $input->getColumnName();
			$context = $this->getContext();
			switch ($vn) {
				case "ipAddress":
					$cidr_notation = _("CIDR notation");
					$placeholder = _("IP address") . " ({$cidr_notation})";
					$input->setLabelString($placeholder);
					if (! $context->isUninitialized()) {
						$input->setValueAttribute($context->getCidrNotation());
					}
					return SUCCESS;
				default:
			}
			return parent::reconfigureInput($input);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		$f = __METHOD__;
		try {
			$context = $this->getContext();
			if ($context->isUninitialized()) {
				return [
					DIRECTIVE_INSERT
				];
			}
			$submits = [
				DIRECTIVE_UPDATE
			];
			if (! $context->getAccessAttempted()) {
				array_push($submits, DIRECTIVE_DELETE);
			}
			return $submits;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getFormDataIndices(): ?array
	{
		return [
			IpAddressDatum::getColumnNameStatic() => CidrIpAddressInput::class,
			'note' => TextInput::class,
			'list' => GhostButton::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "cidr_ip";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/account_firewall';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		$context = $this->getContext();
		$list = $context->getList();
		switch ($name) {
			case DIRECTIVE_INSERT:
				switch ($list) {
					case POLICY_ALLOW:
						$button = $this->generateGenericButton($name);
						$button->setNameAttribute("directive[insert][list]");
						$button->setValueAttribute(POLICY_ALLOW);
						$button->setInnerHTML(_("Authorize"));
						break;
					case POLICY_BLOCK:
						$button = $this->generateGenericButton($name);
						$button->setNameAttribute("directive[insert][list]");
						$button->setValueAttribute(POLICY_BLOCK);
						$button->setInnerHTML(_("Ban"));
						break;
					default:
						Debug::error("{$f} please assign a list type before binding context");
						return null;
				}
				return [
					$button
				];
			case DIRECTIVE_UPDATE:
				$update = $this->generateGenericButton($name);
				$ban = $this->generateGenericButton($name, POLICY_BLOCK);
				$ban->setNameAttribute("directive[update][list]");
				$ban->setInnerHTML(_("Ban"));
				$authorize = $this->generateGenericButton($name, POLICY_ALLOW);
				$authorize->setNameAttribute("directive[update][list]");
				$authorize->setInnerHTML(_("Authorize"));
				$delist = $this->generateGenericButton($name);
				$delist->setNameAttribute("directive[update][list]");
				$delist->setValueAttribute(POLICY_NONE);
				switch ($list) {
					case POLICY_ALLOW:
						$delist->setInnerHTML(_("Deauthorize"));
						if($context->getAccessAttempted()){
							return [
								$delist,
								$ban
							];
						}
						return [
							$update,
							$delist,
							$ban
						];
					case POLICY_BLOCK:
						$delist->setInnerHTML(_("Unban"));
						if($context->getAccessAttempted()){
							return [
								$delist,
								$authorize
							];
						}
						return [
							$update,
							$delist,
							$authorize
						];
					default:
						if($context->getAccessAttempted()){
							return [
								$authorize,
								$ban
							];
						}
						return [
							$update,
							$authorize,
							$ban
						];
				}
			case DIRECTIVE_DELETE:
				return [
					$this->generateGenericButton($name)
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}