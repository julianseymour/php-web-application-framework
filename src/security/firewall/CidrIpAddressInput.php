<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

class CidrIpAddressInput extends TextInput{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setLabelString(_("IP address/range")." ("._("CIDR notation").")");
	}
}
