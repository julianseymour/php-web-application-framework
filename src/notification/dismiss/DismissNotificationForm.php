<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\dismiss;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use Exception;

class DismissNotificationForm extends AjaxForm implements TemplateElementInterface
{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("notification_dismiss");
	}

	public static function isTemplateworthy(): bool
	{
		return true;
	}

	public function bindContext($context)
	{
		$key = new GetColumnValueCommand($context, $context->getIdentifierName()); // $context->getIdentifierValueCommand();
		$this->setIdAttribute(new ConcatenateCommand("dismiss-", $key));
		$this->setAttribute('uniqueKey', $key);
		$this->setAttribute("note_type", new GetColumnValueCommand($context, "notificationType")); // $context->getColumnValueCommand("notificationType"));
		$context = parent::bindContext($context);
		return $context;
	}

	public static function getMethodAttributeStatic(): ?string
	{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getFormDataIndices(): ?array
	{
		$f = __METHOD__; //DismissNotificationForm::getShortClass()."(".static::getShortClass().")->getFormDataIndices()";
		try {
			$indices = [
				'notificationType' => HiddenInput::class,
				NotificationData::getIdentifierNameStatic() => HiddenInput::class
			];
			$context = $this->getContext();
			if ($context->getNotificationType() === NOTIFICATION_TYPE_TEMPLATE || $context->getTypedNotificationClass()::dismissalRequiresCorrespondentKey($context)) {
				$indices["correspondentKey"] = HiddenInput::class;
			}
			return $indices;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getErrorCallbackStatic(): ?string
	{
		return "NotificationData.restoreDismissedNotification";
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "dismiss_notification";
	}

	public static function getActionAttributeStatic(): ?string
	{
		return '/dismiss';
	}

	public function generateButtons(string $name): ?array
	{
		$button = $this->generateGenericButton($name, $this->getIdAttribute());
		$button->setAllocationMode($this->getAllocationMode());
		$button->setInnerHTML(new ConcatenateCommand("🗄", _("Dismiss")));
		$onclick = "NotificationData.dismissNotification(event, this)";
		$button->setOnClickAttribute($onclick);
		return [
			$button
		];
	}

	public function getDirectives(): ?array
	{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public static function getTemplateContextClass(): string
	{
		return RetrospectiveNotificationData::class;
	}
}
