<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\correspondent\UserCorrespondence;
use JulianSeymour\PHPWebApplicationFramework\account\owner\OwnerPermission;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubjectiveTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationIdSuffixCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\messenger\RetrospectiveMessage;
use Exception;
use mysqli;

abstract class NotificationData extends UserCorrespondence implements StaticElementClassInterface
{

	use JavaScriptCounterpartTrait;
	use SubjectiveTrait;

	public abstract function getTypedNotificationClass();

	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(NotificationData::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public function getNotificationLinkUri(){
		$typed_class = $this->getTypedNotificationClass();
		return $typed_class::getNotificationLinkUriStatic($this);
	}

	public function getSubtypeValue(){
		return $this->getNotificationType();
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch ($column_name) {
			case "dismissable":
				return $this->isDismissable();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public function getNotificationPreview(){
		$element_class = $this->getElementClass();
		$element = new $element_class(ALLOCATION_MODE_NEVER, $this);
		$preview = $element->getNotificationPreview();
		while ($preview instanceof ValueReturningCommandInterface) {
			$preview = $preview->evaluate();
		}
		$element = null;
		return $preview;
	}

	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		try {
			switch ($column_name) {
				case "actions":
					$arr = $this->getTypedNotificationClass()::getNotificationActionsStatic($this);
					if ($this->isDismissable()) {
						array_push($arr, [
							"action" => "dismiss",
							"title" => _("Dismiss")
						]);
					}
					return $arr;
				case "dismissable":
					return $this->isDismissable();
				case "linkUri":
					return $this->getNotificationLinkUri();
				case "preview":
					return $this->getNotificationPreview();
				case "title":
					return $this->getNotificationTitle();
				default:
					// Debug::warning("{$f} invalid virtual datum index \"{$column_name}\"; returning parent function");
					return parent::getVirtualColumnValue($column_name);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public final function getNotificationTitle(){
		$f = __METHOD__;
		$element_class = $this->getElementClass();
		$element = new $element_class(ALLOCATION_MODE_NEVER, $this);
		$title = $element->getNotificationTitle();
		while ($title instanceof ValueReturningCommandInterface) {
			$title = $title->evaluate();
		}
		Debug::print("{$f} notification title is \"{$title}\"");
		$element = null;
		return $title;
	}

	public function getElementIdSuffix(){
		return $this->getIdentifierValue();
	}

	public function getIdAttributeSuffixCommand(){
		return new NotificationIdSuffixCommand($this);
	}

	public static function getTableNameStatic(): string{
		return "notifications";
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	/**
	 *
	 * @param UserData $user
	 * {@inheritdoc}
	 * @see UserCorrespondence::setUserData()
	 */
	public function setUserData(UserData $user):UserData{
		$f = __METHOD__;
		try {
			$ndt = $user->getNotificationDeliveryTimestamp();
			$this->setOldDeliveryTimestamp($ndt);
			return parent::setUserData($user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"push"
		]);
	}

	public function setPushFlag($v){
		return $this->setFlag("push", $v);
	}

	public function getPushFlag(){
		return $this->getFlag("push");
	}

	public function getOldDeliveryTimestamp(){
		return $this->getColumnValue("oldTimestamp");
	}

	public function setOldDeliveryTimestamp($ndt){
		return $this->setColumnValue("oldTimestamp", $ndt);
	}

	public function requiresAttention(){
		$f = __METHOD__;
		if (! $this->hasSubjectData()) {
			Debug::error("{$f} target object is undefined");
		}
		$target = $this->getSubjectData();
		return $target->requiresAttention();
	}

	protected function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasNotificationState()) {
			$this->setNotificationState(NOTIFICATION_STATE_UNREAD);
		}
		if ($this->hasCorrespondentKey()) {
			$ck = $this->getCorrespondentKey();
			if ($print) {
				Debug::print("{$f} correspondent key is \"{$ck}\"");
			}
		} elseif ($print) {
			Debug::print("{$f} this notification does not have a correspondent key");
		}

		if ($print) {
			if ($this->hasCorrespondentAccountType()) {
				$type = $this->getCorrespondentAccountType();
				Debug::print("{$f} correspondent account type is \"{$type}\"");
			} else {
				Debug::print("{$f} this notification does not have a correspondent account type");
			}
		}
		return parent::beforeInsertHook($mysqli);
	}

	protected static function getRetrospectiveMessageClass(){
		return RetrospectiveMessage::class;
	}

	public function acquireCorrespondentObject(mysqli $mysqli){
		$f = __METHOD__;
		try {
			// Debug::print("{$f} entered");
			if ($this->getNotificationType() === NOTIFICATION_TYPE_SECURITY) {
				Debug::error("{$f} this should not get called for security notifications");
			}
			// Debug::print("{$f} about to call parent function");
			$correspondent = parent::acquireCorrespondentObject($mysqli);
			if (! isset($correspondent)) {
				Debug::error("{$f} acquireCorrespondentObject returned null");
			}
			// Debug::print("{$f} returning a perfectly valid correspondent object");
			return $correspondent;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function skipCorrespondentAcquisition(){
		return $this->getNotificationType() === NOTIFICATION_TYPE_SECURITY ? true : false;
	}

	public function setNotificationCount($count){
		return $this->setColumnValue('notificationCount', $count);
	}

	protected function afterSetForeignDataStructureHook(string $columnName, DataStructure $struct): int{
		switch ($columnName) {
			case "subjectKey":
				$struct->setAutoloadFlags(true);
				break;
			default:
		}
		return parent::afterSetForeignDataStructureHook($columnName, $struct);
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::reconfigureColumns($columns, $ds);
			$indices = [
				// "correspondentName",
				"correspondentTemporaryRole",
				// "userName",
				"userTemporaryRole"
			];
			foreach ($indices as $column_name) {
				if (! array_key_exists($column_name, $columns)) {
					Debug::printArray(array_keys($columns));
					Debug::error("{$f} column \"{$column_name}\" does not exist");
				}
				$columns[$column_name]->volatilize();
			}
			$columns['correspondentAccountType']->setNullable(true);
			$columns['correspondentKey']->setNullable(true);
			$columns['correspondentKey']->setDefaultValue(null);
			$columns["correspondentKey"]->autoload();
			$columns['correspondentNameKey']->setNullable(true);
			$columns['userNameKey']->setNullable(true);
			// $columns['userKey']->setAutoloadFlag();
		} catch (Exception $x) {
			x($f, x);
		}
	}

	public function getCorrespondentName():string{
		$f = __METHOD__;
		try{
			if($this->getTypedNotificationClass()::noCorrespondent()){
				// Debug::print("{$f} this is a security notification, it doesn't have a correspondent");
				return _("N/A");
			} elseif (! $this->hasCorrespondentKey()) {
				return _("Deleted correspondent");
			}
			return parent::getCorrespondentName();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getPinnedTimestamp(){
		return $this->getColumnValue("pinnedTimestamp");
	}

	public function setPinnedTimestamp($timestamp){
		return $this->setColumnValue("pinnedTimestamp", $timestamp);
	}

	public function hasPinnedTimestamp(){
		return $this->hasColumnValue("pinnedTimestamp");
	}

	public function getName(){
		$f = __METHOD__;
		try {
			if ($this->getNotificationType() === NOTIFICATION_TYPE_SECURITY) {
				return _("Security notification");
			} elseif ($this->hasCorrespondentObject()) {
				return $this->getCorrespondentName();
			}
			// Debug::print("{$f} this is not a security notification");
			return substitute(_("Notification #%1%"), $this->getContext->getSerialnumber());
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getNotificationType(){
		return $this->getColumnValue('notificationType');
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::declareColumns($columns, $ds);
			$type = new StringEnumeratedDatum("notificationType");
			$type->setValidEnumerationMap(array_keys(mods()->getTypedNotificationClasses()));
			$type->setHumanReadableName(_("Notification type"));
			$count = new UnsignedIntegerDatum("notificationCount", 8);
			$count->setDefaultValue(1);
			$state = new StringEnumeratedDatum("notificationState");
			$state->setValidEnumerationMap([
				NOTIFICATION_STATE_UNREAD,
				NOTIFICATION_STATE_DISMISSED
			]);
			// $state->setDefaultValue(NOTIFICATION_STATE_UNDEFINED);
			$subjectKey = new ForeignMetadataBundle("subject", $ds);
			$subjectKey->setAutoloadFlag(true);
			$subjectKey->setForeignDataStructureClassResolver(NotificationSubjectClassResolver::class);
			$subjectKey->setNullable(true);
			$subjectKey->setOnDelete($subjectKey->setOnUpdate(REFERENCE_OPTION_CASCADE));
			$subjectKey->setNullable(true);
			$subjectKey->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);

			$pin_timestamp = new TimestampDatum("pinnedTimestamp");
			$pin_timestamp->setUpdateToCurrentTimeFlag(true);
			$pin_timestamp->setNullable(true);
			$pin_timestamp->setDefaultValue(null);
			$pin_timestamp->setHumanReadableName(_("Pinned timestamp"));
			$pin_timestamp->setTrimmableFlag(false);

			$dismissable = new VirtualDatum("dismissable");
			$link_uri = new VirtualDatum("linkUri");
			$preview = new VirtualDatum("preview");
			$title = new VirtualDatum("title");

			$oldTimestamp = new TimestampDatum("oldTimestamp");
			$oldTimestamp->volatilize();

			$actions = new VirtualDatum("actions");
			static::pushTemporaryColumnsStatic($columns, $type, $count, $state, $pin_timestamp, $dismissable, $link_uri, $preview, $title, $actions, $subjectKey, $oldTimestamp);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getNotificationCount(){
		return $this->getColumnValue('notificationCount');
	}

	public function hasNotificationState(): bool{
		return $this->hasColumnValue("notificationState");
	}

	public function getNotificationState(){
		return $this->getColumnValue("notificationState");
	}

	public function setNotificationState($s){
		return $this->setColumnValue('notificationState', $s);
	}

	public static final function getDataType(): string{
		return DATATYPE_NOTIFICATION;
	}

	public function setNotificationType($t){
		return $this->setColumnValue('notificationType', $t);
	}

	public static function getNotificationStateStringStatic($status){
		$f = __METHOD__;
		switch ($status) {
			case NOTIFICATION_STATE_UNREAD:
				return _("Unread");
			case NOTIFICATION_STATE_DISMISSED:
				return _("Dismissed");
			default:
				Debug::error("{$f} invalid notification status \"{$status}\"");
		}
	}

	public function getNotificationStateString(){
		return static::getNotificationStateStringStatic($this->getNotificationState());
	}

	public function excludeFromPhylumArray(){
		$f = __METHOD__;
		$print = false;
		if ($this->getNotificationState() === NOTIFICATION_STATE_DISMISSED) {
			if ($print) {
				Debug::print("{$f} this notification was dismissed; ecxlude it");
			}
			return true;
		} elseif ($print) {
			Debug::print("{$f} this notification has not been dismissed; include it");
		}
		return false;
	}

	public function isDismissable(){
		return $this->getTypedNotificationClass()::isDismissableStatic($this);
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		$print = false;
		$sub_id = $config_id === "push" ? CONST_DEFAULT : $config_id;
		$config = parent::getArrayMembershipConfiguration($sub_id);
		if ($print) {
			Debug::print("{$f} parent function returned the following array:");
			Debug::printArray($config);
		}
		if (array_key_exists("name", $config)) {
			Debug::error("{$f} notifications don't have names");
		}
		$class = $this->getTypedNotificationClass();
		return array_merge($config, $class::getArrayMembershipConfigurationStatic($config_id, $this));
	}

	public static function getArrayMembershipConfigurationStatic($config_id, $that){
		$sub_id = $config_id === "push" ? CONST_DEFAULT : $config_id;
		switch ($config_id) {
			case "push":
				$config['actions'] = true;
				$config['preview'] = true;
				$config['title'] = true;
				$config_id = CONST_DEFAULT;
			case CONST_DEFAULT:
			default:
				// $config['correspondentDisplayName'] = true;
				$config['dismissable'] = $that->isDismissable();
				// $config['linkUri'] = true;
				$config['notificationCount'] = true;
				$config['notificationState'] = true;
				$config["notificationType"] = true;
				$config["pinnedTimestamp"] = $that->hasPinnedTimestamp();
				$config['subjectKey'] = $that->hasSubjectData() ? $that->getSubjectData()->getArrayMembershipConfiguration($sub_id) : true;
				$config['subjectDataType'] = $that->hasSubjectData();
				$config['subjectSubtype'] = $that->hasSubjectData();
				break;
		}
		return $config;
	}

	public function getUserTemporaryRole(){
		return $this->getUserData()->getTemporaryRole();
	}

	public static function getPhylumName(): string{
		return "notifications";
	}

	public static function userIsParent(){
		return true;
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("Notification");
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Notifications");
	}

	public function getPushNotificationDeliverable(): string{
		$f = __METHOD__;
		$print = false;
		if (! $this instanceof NotificationData) {
			Debug::error("{$f} you were supposed to send a notification data structure");
		}
		if ($print) {
			Debug::print("{$f} about to send push notification");
		}
		$recipient = $this->getUserData();
		$ck = $this->getUserKey();
		if ($print) {
			Debug::print("{$f} about to encrypt user key \"{$ck}\"");
		}
		$arr = [
			'num_cipher_64' => base64_encode($recipient->encrypt($this->getSerialNumber())),
			'user_key_cipher_64' => base64_encode($recipient->encrypt($ck)),
			"username" => $recipient->getName(),
			'random' => sha1(random_bytes(20))
		];
		return json_encode($arr);
	}

	public static function getPermissionStatic(string $name, $data){
		$f = __METHOD__;
		try {
			if ($data->hasPermission($name)) {
				return $data->getPermission($name);
			}
			switch ($name) {
				case DIRECTIVE_INSERT:
					return new Permission($name, function (PlayableUser $user, NotificationData $notification) {
						if ($user instanceof Administrator) {
							return SUCCESS;
						} elseif ($notification->isOwnedBy($user)) {
							return SUCCESS;
						} elseif ($user->getIdentifierValue() === $notification->getCorrespondentKey()) {
							return SUCCESS;
						}
						return FAILURE;
					});
				case DIRECTIVE_UPDATE:
					return new OwnerPermission($name);
				default:
					return parent::getPermissionStatic($name, $data);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

