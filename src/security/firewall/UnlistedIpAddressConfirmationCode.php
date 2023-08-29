<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\AnonymousConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

class UnlistedIpAddressConfirmationCode extends AnonymousConfirmationCode{

	/**
	 *
	 * @return ListedIpAddress
	 */
	public function getIpAddressObject(){
		$f = __METHOD__;
		if (! $this->hasIpAddressObject()) {
			Debug::error("{$f} IP address object is undefined");
		}
		return $this->getForeignDataStructure("ipAddressKey");
	}

	public function getSubtypeValue(): string{
		return ACCESS_TYPE_UNLISTED_IP_ADDRESS;
	}

	public function extractAdditionalDataFromUser($user){
		$this->setIpAddressObject($user->getRequestEventObject());
		return parent::extractAdditionalDataFromUser($user);
	}

	public function setIpAddressObject($obj){
		return $this->setForeignDataStructure("ipAddressKey", $obj);
	}

	public function hasIpAddressObject():bool{
		return $this->hasForeignDataStructure('ipAddressKey');
	}

	public function getIpAddress(){
		return $this->getIpAddressObject()->getIpAddress();
	}

	public function getIpAddressKey(){
		$f = __METHOD__;
		if ($this->hasIpAddressKey()) {
			return $this->getColumnValue('ipAddressKey');
		} elseif (! $this->hasIpAddressObject()) {
			Debug::error("{$f} listed IP address object is undefined");
		}
		$listed_ip = $this->getIpAddressObject();
		$key = $listed_ip->getIdentifierValue();
		return $this->setIpAddressKey($key);
	}

	public function setIpAddressKey($key){
		return $this->setColumnValue('ipAddressKey', $key);
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	/**
	 * unlisted IPs always send their own security notification so a sending another one for the
	 * confirmation code is redundant
	 */
	public function isSecurityNotificationWarranted():bool{
		return false;
	}

	public static function getSentEmailStatus(){
		return RESULT_CIDR_UNMATCHED;
	}

	public static function getConfirmationUriStatic($suffix){
		return WEBSITE_URL . "/authorize_ip/{$suffix}";
	}

	public static function getEmailNotificationClass(){
		return UnlistedIpAddressEmail::class;
	}

	public static function getConfirmationCodeTypeStatic(){
		return ACCESS_TYPE_UNLISTED_IP_ADDRESS;
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_UNLISTED_IP;
	}

	public static function getTableNameStatic(): string{
		return "ip_address_confirmation_codes";
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__; //UnlistedIpAddressConfirmationCode::getShortClass()."(".static::getShortClass().")::declareColumns()";
		parent::declareColumns($columns, $ds);
		$ip_address_key = new ForeignKeyDatum('ipAddressKey');
		$ip_address_key->setForeignDataStructureClass(ListedIpAddress::class);
		$ip_address_key->setAutoloadFlag(true);
		$ip_address_key->setOnUpdate($ip_address_key->setOnDelete(REFERENCE_OPTION_CASCADE));
		$ip_address_key->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		static::pushTemporaryColumnsStatic($columns, $ip_address_key);
	}

	public function isEmailNotificationWarranted($recipient): bool{
		return $recipient->getAuthLinkEnabled();
	}
}
