<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\online;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class UpdateOnlineStatusIndicatorCommand extends Command implements JavaScriptCounterpartInterface, JavaScriptInterface, ServerExecutableCommandInterface{

	use JavaScriptCounterpartTrait;

	protected $correspondent;

	protected $indicator;

	public function setCorrespondentObject(UserData $correspondent):UserData{
		$f = __METHOD__;
		if (! $correspondent instanceof PlayableUser) {
			Debug::error("{$f} correspondent is not an instanceof PlayableUser");
		}
		return $this->correspondent = $correspondent;
	}

	public function hasCorrespondentObject():bool{
		return isset($this->correspondent);
	}

	public function getCorrespondent(): ?UserData{
		$f = __METHOD__;
		if (! $this->hasCorrespondentObject()) {
			Debug::error("{$f} correspondent object is undefined");
		}
		return $this->correspondent;
	}

	public function __construct($correspondent, $indicator = null){
		$f = __METHOD__;
		parent::__construct();
		$this->setCorrespondentObject($correspondent);
		if (isset($indicator)) {
			$this->setIndicator($indicator);
		}
	}

	public function setIndicator($indicator){
		return $this->indicator = $indicator;
	}

	public function hasIndicator():bool{
		return isset($this->indicator) && $this->indicator instanceof OnlineStatusIndicator;
	}

	public function getIndicator(){
		$f = __METHOD__;
		if (! $this->hasIndicator()) {
			Debug::error("{$f} indicator is undefined");
		}
		return $this->indicator;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		$correspondent = $this->getCorrespondent();
		$user = user();
		Json::echoKeyValuePair('uniqueKey', $correspondent->getIdentifierValue(), $destroy);
		$online = $correspondent->getVisibleOnlineStatus($user);
		Json::echoKeyValuePair('status', $online, $destroy);
		if ($online === ONLINE_STATUS_CUSTOM) {
			Json::echoKeyValuePair('custom_str', $correspondent->getCustomOnlineStatusString(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->correspondent);
		unset($this->indicator);
	}

	public static function getCommandId(): string{
		return "online";
	}

	public function resolve(){
		$f = __METHOD__;
		try {
			$correspondent = $this->getCorrespondent();
			$user = user();
			$online = $correspondent->getVisibleOnlineStatus($user);
			$indicator = $this->getIndicator();
			switch ($online) {
				case ONLINE_STATUS_NONE:
					// Debug::print("{$f} correspondent does not share their online status, good for them");
					$indicator->setAllocationMode(ALLOCATION_MODE_NEVER);
					break;
				case ONLINE_STATUS_UNDEFINED:
					Debug::warning("{$f} undefined messenger status");
					$indicator->setInnerHTML("💀 ERROR");
					$indicator->setStyleProperty("color", "#f00");
					break;
				case ONLINE_STATUS_OFFLINE:
					// Debug::print("{$f} correspondent is offline");
					$status_string = strtolower(_("Offline"));
					$indicator->setInnerHTML("😴 {$status_string}");
					$indicator->setStyleProperty("color", "#555");
					break;
				case ONLINE_STATUS_ONLINE:
					// Debug::print("{$f} correspondent was online recently");
					$status_string = strtolower(_("Online"));
					$indicator->setInnerHTML("⬤ {$status_string}");
					$indicator->setStyleProperty("color", "#0c0");
					break;
				case ONLINE_STATUS_APPEAR_OFFLINE:
					// Debug::print("{$f} correspondent is pretending to be offline");
					$status_string = strtolower(_("Invisible"));
					$indicator->setInnerHTML("👻 {$status_string}");
					$indicator->setStyleProperty("color", "#555");
					break;
				case ONLINE_STATUS_AWAY:
					// Debug::print("{$f} correspondent is away");
					$status_string = strtolower(_("Away"));
					$indicator->setInnerHTML("⚠️ {$status_string}");
					$indicator->setStyleProperty("color", "#ff0");
					break;
				case ONLINE_STATUS_BUSY:
					// Debug::print("{$f} correspondent is busy");
					$status_string = strtolower(_("Busy"));
					$indicator->setInnerHTML("🛑 {$status_string}");
					$indicator->setStyleProperty("color", "#f00");
					break;
				case ONLINE_STATUS_CUSTOM:
					// Debug::print("{$f} correspondent has custom messenger status");
					$indicator->setInnerHTML($correspondent->getCustomOnlineStatusString());
					$indicator->setStyleProperty("color", "#0c0");
					break;
				default:
					Debug::error("{$f} Invalid messenger status \"{$online}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try {
			$correspondent = $this->getCorrespondent();
			$key = new GetColumnValueCommand($correspondent, $correspondent->getIdentifierName());
			if ($key instanceof JavaScriptInterface) {
				$key = $key->toJavaScript();
			} elseif (is_string($key) || $key instanceof StringifiableInterface) {
				$key = single_quote($key);
			}
			return "UpdateOnlineStatusIndicatorCommand.updateStatic({$key})";
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
