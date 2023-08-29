<?php
namespace JulianSeymour\PHPWebApplicationFramework\security;

use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\AnchorElement;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationElement;

class SecurityNotificationElement extends NotificationElement{

	public function bindContext($context){
		$f = __METHOD__;
		$ret = parent::bindContext($context);
		$this->addClassAttribute("security_notification");
		return $ret;
	}

	public function getNotificationContent(){
		$f = __METHOD__;
		$mode = $this->getAllocationMode();
		$notification_content = new AnchorElement($mode);
		$notification_content->addClassAttribute("notification_content");
		$notification_content->setHrefAttribute('/account_firewall');
		$notification_title = new DivElement($mode);
		$notification_title->addClassAttribute("notification_title");
		$notification_title->setInnerHTML($this->getNotificationTitle());
		$notification_content->appendChild($notification_title);
		$notification_preview = new DivElement($mode);
		$notification_preview->addClassAttribute("notification_preview");
		$div1 = new DivElement($mode);
		$div1->addClassAttribute("preview");
		$div1->setAllowEmptyInnerHTML(true);
		$scope = $this->getResolvedScope();
		$div1->setInnerHTML($this->getNotificationPreview());
		$notification_preview->appendChild($div1);
		$div2 = new DivElement($mode);
		$div2->appendChild(CommandBuilder::createTextNode(CommandBuilder::concatenate(_("IP address"), " ", $scope->getDeclaredVariableCommand("insertIpAddress"))));
		$notification_preview->appendChild($div2);
		$notification_content->appendChild($notification_preview);
		return $notification_content;
	}

	public function getNotificationTitle(){
		return _("Security alert");
	}

	public function getNotificationPreview(){
		return $this->getResolvedScope()->getDeclaredVariableCommand("reasonLoggedString");
	}

	protected function getScopeResolutionCommands($context, Scope $scope): ?array{
		return [
			...$scope->letNames("reasonLoggedString", "insertIpAddress"),
			CommandBuilder::if($context->hasForeignDataStructureCommand("subjectKey"))->then($scope->let("subject", new GetForeignDataStructureCommand($context, "subjectKey")), ...$scope->redeclareColumnValues($scope->getDeclaredVariableCommand("subject"), "reasonLoggedString", "insertIpAddress"))->else(...$scope->redeclareMultiple([
				"reasonLoggedString" => _("Unknown reason"),
				"insertIpAddress" => _("Unknown IP address")
			]))
		];
	}
}
