<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class Administrator extends AuthenticatedUser{

	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		parent::__construct($mode);
		$this->setAccountType($this->getAccountTypeStatic());
	}

	public static function getFullAuthenticationDataClass(): string{
		return AdminAuthenticationData::class;
	}

	public function getMessageBox(): int{
		return MESSAGE_BOX_OUTBOX;
	}

	public function getVirtualColumnValue($index){
		$f = __METHOD__;
		try {
			switch ($index) {
				case "messageBody":
					return _("New conversation");
				default:
					return parent::getVirtualColumnValue($index);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getArrayMembershipConfiguration($config_id): array{
		$f = __METHOD__;
		try {
			$config = parent::getArrayMembershipConfiguration($config_id);
			if (is_string($config_id)) {
				switch ($config_id) {
					case CONST_DEFAULT:
					default:
						$config['messageBody'] = true;
				}
			}
			return $config;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$body = new VirtualDatum("messageBody");
		static::pushTemporaryColumnsStatic($columns, $body);
	}

	public function encryptAdminCopy($data){
		return $this->encrypt($data);
	}

	public function getAccountType(): string{
		return static::getAccountTypeStatic();
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$columns['accountType']->volatilize();
	}

	public static function getTableNameStatic(): string{
		return "administrators";
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @return Administrator
	 */
	public static function getAdministratorStatic(mysqli $mysqli): Administrator{
		$f = __METHOD__;
		try {
			$class = static::class;
			$admin = new $class();
			$admin->setSerialNumber(1);
			$status = $admin->load($mysqli, "num", 1);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} failed to load admin #1: \"{$err}\"");
			}
			return $admin;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function beforeDeleteHook(mysqli $mysqli): int{
		return $this->setObjectStatus(ERROR_INTERNAL);
	}

	public function isAccountActivated(): bool{
		return true;
	}

	public function getWebsiteUrl(): string{
		return WEBSITE_URL;
	}

	public function writeUpdatedNotificationDeliveryTimestamp($mysqli): int{
		$f = __METHOD__;
		try {
			$st = QueryBuilder::update(NormalUser::getDatabaseNameStatic(), NormalUser::getTableNameStatic())->set([
				"notificationDeliveryTimestamp" => '?'
			])->where(new WhereCondition(NormalUser::getIdentifierNameStatic(), OPERATOR_EQUALS))->prepareBindExecuteGetStatement($mysqli, 'is', $this->setNotificationDeliveryTimestamp(time()), $this->getIdentifierValue());
			if ($st == null) {
				$status = $this->getObjectStatus();
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} updating notification delivery timestamp returned error status \"{$err}\"");
				return $this->getObjectStatus();
			}
			return $this->getObjectStatus();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isAdminStub(): bool{
		return true;
	}

	public function getAttachmentsEnabled(): bool{
		return true;
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("Administrator");
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Administrators");
	}

	public static function getAccountTypeStatic(){
		return ACCOUNT_TYPE_ADMIN;
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_UPDATE:
				return new AdminOnlyAccountTypePermission($name);
			case DIRECTIVE_INSERT:
				return FAILURE; // XXX
			default:
		}
		return parent::getPermissionStatic($name, $data);
	}
}

