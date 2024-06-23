<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\online;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\NullCommand;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetConstantCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class UpdateOnlineStatusIndicatorCommand extends Command 
implements JavaScriptCounterpartInterface, JavaScriptInterface, ServerExecutableCommandInterface{

	use JavaScriptCounterpartTrait;

	protected $correspondent;

	protected $indicator;

	public function __construct($correspondent=null, $indicator=null){
		$f = __METHOD__;
		parent::__construct();
		if($indicator !== null){
			$this->setCorrespondentObject($correspondent);
		}
		if($indicator !== null){
			$this->setIndicator($indicator);
		}
	}

	public function setCorrespondentObject(UserData $correspondent):UserData{
		$f = __METHOD__;
		if(!$correspondent instanceof PlayableUser){
			Debug::error("{$f} correspondent is not an instanceof PlayableUser");
		}
		if($this->hasCorrespondentObject()){
			$this->release($this->correspondent);
		}
		return $this->correspondent = $this->claim($correspondent);
	}

	public function hasCorrespondentObject():bool{
		return isset($this->correspondent);
	}

	public function getCorrespondent(): ?UserData{
		$f = __METHOD__;
		if(!$this->hasCorrespondentObject()){
			Debug::error("{$f} correspondent object is undefined");
		}
		return $this->correspondent;
	}

	public function setIndicator($indicator){
		if($this->hasIndicator()){
			unset($this->indicator);
		}
		return $this->indicator = $indicator;
	}

	public function hasIndicator():bool{
		return isset($this->indicator);
	}

	public function getIndicator(){
		$f = __METHOD__;
		if(!$this->hasIndicator()){
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
		if($online === ONLINE_STATUS_CUSTOM){
			Json::echoKeyValuePair('custom_str', $correspondent->getCustomOnlineStatusString(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->correspondent, $deallocate);
		unset($this->indicator);
	}

	public static function getCommandId(): string{
		return "online";
	}

	public function resolve(){
		$f = __METHOD__;
		try{
			$correspondent = $this->getCorrespondent();
			$user = user();
			$online = $correspondent->getVisibleOnlineStatus($user);
			$indicator = $this->getIndicator();
			switch($online){
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
					$status_string = _("Offline");
					$indicator->setInnerHTML("😴 {$status_string}");
					$indicator->setStyleProperty("color", "#555");
					break;
				case ONLINE_STATUS_ONLINE:
					// Debug::print("{$f} correspondent was online recently");
					$status_string = _("Online");
					$indicator->setInnerHTML("⬤ {$status_string}");
					$indicator->setStyleProperty("color", "#0c0");
					break;
				case ONLINE_STATUS_APPEAR_OFFLINE:
					// Debug::print("{$f} correspondent is pretending to be offline");
					$status_string = _("Invisible");
					$indicator->setInnerHTML("👻 {$status_string}");
					$indicator->setStyleProperty("color", "#555");
					break;
				case ONLINE_STATUS_AWAY:
					// Debug::print("{$f} correspondent is away");
					$status_string = _("Away");
					$indicator->setInnerHTML("⚠️ {$status_string}");
					$indicator->setStyleProperty("color", "#ff0");
					break;
				case ONLINE_STATUS_BUSY:
					// Debug::print("{$f} correspondent is busy");
					$status_string = _("Busy");
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
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$correspondent = $this->getCorrespondent();
			$status = new GetColumnValueCommand($correspondent, 'onlineStatus');
			$null = new NullCommand();
			$let1 = DeclareVariableCommand::let('custom_str');
			$let2 = DeclareVariableCommand::let("online_status");
			$null_custom_string = DeclareVariableCommand::redeclare("custom_str", $null);
			$if = IfCommand::if(new HasColumnValueCommand($correspondent, "onlineStatus"))->then(
				DeclareVariableCommand::redeclare("online_status", $status),
				IfCommand::if(
					new AndCommand(
						new BinaryExpressionCommand(
							$status,
							OPERATOR_EQUALSEQUALS,
							new GetConstantCommand("ONLINE_STATUS_CUSTOM")
						),
						new HasColumnValueCommand($correspondent, "customOnlineStatusString")
					)
				)->then(
					DeclareVariableCommand::redeclare(
						"custom_str", 
						new GetColumnValueCommand($correspondent, "customOnlineStatusString")
					)
				)->else($null_custom_string)
			)->else(
				DeclareVariableCommand::redeclare(
					"online_status", 
					new GetConstantCommand("ONLINE_STATUS_NONE")
				),
				$null_custom_string
			);
			$cf = new CallFunctionCommand(
				"UpdateOnlineStatusIndicatorCommand.updateStatic", 
				new GetColumnValueCommand($correspondent, $correspondent->getIdentifierName()), 
				new GetDeclaredVariableCommand("online_status"),
				new GetDeclaredVariableCommand("custom_str")
			);
			$ret = "";
			$ret .= $let1->toJavaScript().";";
			$ret .= $let2->toJavaScript().";";
			$ret .= $if->toJavaScript();
			$ret .= $cf->toJavaScript().";";
			deallocate($let1);
			deallocate($let2);
			deallocate($if);
			deallocate($cf);
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
