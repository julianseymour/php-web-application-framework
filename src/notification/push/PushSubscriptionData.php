<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class PushSubscriptionData extends UserFingerprint implements StaticTableNameInterface{

	use StaticTableNameTrait;
	
	protected $minishlinkSubscription = null;

	public function enqueueRemoteBackup($mysqli){
		return SUCCESS;
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::reconfigureColumns($columns, $ds);
			$indices = [
				"userName",
				// "userNormalizedName",
				"userTemporaryRole",
				'reasonLogged'
			];
			foreach($indices as $field) {
				$columns[$field]->volatilize();
			}
			$columns["userNameKey"]->setNullable(true);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_DELETE:
				return SUCCESS;
			default:
				break;
		}
		return parent::getPermissionStatic($name, $data);
	}

	public static function getTableNameStatic(): string{
		return "push_subscriptions";
	}

	public static function getDataType(): string{
		return DATATYPE_PUSH_SUBSCRIPTION;
	}

	public function getPushAPIEndpoint(){
		return $this->getColumnValue('endpoint');
	}

	public function setPushAPIEndpoint($endpoint){
		$f = __METHOD__;
		try{
			return $this->setColumnValue('endpoint', $endpoint);
			if(!$this->getUserData() instanceof AnonymousUser) {
				$signature = $this->signMessage($endpoint);
				$this->setSignature($signature);
			}
			return $endpoint;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getAuthPushAPIKey(){
		return $this->getColumnValue('auth');
	}

	public function setAuthPushAPIKey($auth){
		return $this->setColumnValue('auth', $auth);
	}

	public function getP256dhPushAPIKey(){
		return $this->getColumnValue('p256dh');
	}

	public function setP256dhPushAPIKey($key){
		return $this->setColumnValue('p256dh', $key);
	}

	public static function getPhylumName(): string{
		return "pushSubscriptions";
	}

	public function getName():string{
		return substitute(_("Push subscription data for user %1%"), $this->getUserName());
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch ($config_id) {
			case "default":
				$config['endpoint'] = true;
				$config['p256dh'] = true;
				$config['auth'] = true;
			default:
		}
		return $config;
	}

	public static function getCompositeUniqueColumnNames(): ?array{
		return [
			[
				"endpoint"
			]
		];
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_HASH;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$endpoint = new TextDatum("endpoint");
		$p256dh = new TextDatum("p256dh");
		$push_auth = new TextDatum("auth");
		array_push($columns, $endpoint, $p256dh, $push_auth);
	}

	public function sendPushNotification(string $json){
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} user ".$this->getUserData()->getUnambiguousName());
			}
			$auth = [
				'VAPID' => [
					'subject' => WEBSITE_URL,
					'publicKey' => PUSH_API_SERVER_PUBLIC_KEY,
					'privateKey' => VAPID_PRIVATE_KEY
				]
			];
			$webPush = new \Minishlink\WebPush\WebPush($auth);
			$webPush->queueNotification($this->toMinishlinkSubscription(), $json);
			foreach($webPush->flush() as $report) {
				$endpoint = $report->getRequest()->getUri()->__toString();
				if(!$report->isSuccess()) {
					$reason = $report->getReason();
					Debug::warning("{$f} Message failed to send for subscription {$endpoint}: {$reason}");
				}
				if($print) {
					$string = $report->getRequestPayload();
					Debug::print("{$f} request payload: \"{$string}\"");
				}
				$response = $report->getResponse();
				$http_status_code = $response->getStatusCode();
				if($print) {
					Debug::print("{$f} response status code is \"{$http_status_code}\"");
				}
				if($http_status_code == 410) {
					if($print) {
						Debug::warning("{$f} shit's gone forever, time to move on");
					}
					$mysqli = db()->getConnection(PublicWriteCredentials::class);
					$status = $this->delete($mysqli);
					if($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} push subscription deletion returned error status \"{$err}\"");
					}elseif($print) {
						Debug::print("{$f} successfully deleted push subscription data");
					}
				}
			}
			if($print) {
				Debug::print("{$f} done iterating over web push flush, and print is true");
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setMinishlinkSubscription($subscription){
		return $this->minishlinkSubscription = $subscription;
	}

	public function toMinishlinkSubscription(){
		$f = __METHOD__;
		try{
			if(isset($this->minishlinkSubscription)) {
				return $this->minishlinkSubscription;
			}
			$endpoint = $this->getPushAPIEndpoint();
			if($endpoint == null) {
				Debug::error("{$f} endpoint is null");
			}
			$publicKey = $this->getP256dhPushAPIKey();
			if($publicKey == null) {
				Debug::error("{$f} public key is undefined");
			}
			$auth = $this->getAuthPushAPIKey();
			if($auth == null) {
				Debug::error("{$f} auth is null");
			}
			$subscription = new \Minishlink\WebPush\Subscription($endpoint, $publicKey, $auth);
			return $this->setMinishlinkSubscription($subscription);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getPrettyClassName():string{
		return _("Push subscription");
	}

	public static function getPrettyClassNames():string{
		return _("Push subscriptions");
	}
}
	