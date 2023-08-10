<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\security\AccessControlPanelElement;
use Exception;

class AccountFirewallElement extends AccessControlPanelElement{

	public function generateChildNodes(): ?array{
		$context = $this->getContext();
		$mode = $this->getAllocationMode();
		$filter_policy_form = new FilterPolicyForm();
		$filter_policy_form->setAllocationMode($mode);
		$filter_policy_form->bindContext($context);
		$this->appendChild($filter_policy_form);
		return parent::generateChildNodes();
	}
	
	public static function getFormClass(): string{
		return CidrIpAddressForm::class;
	}

	public static function allowNewEntry(): bool{
		return true;
	}

	public static function getDataStructureClass(): string{
		return ListedIpAddress::class;
	}
}
