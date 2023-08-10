<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

interface WidgetInterface extends OpenAndClosableElementInterface
{

	public static function getIdAttributeStatic();

	public static function getWidgetLabelId();

	public static function meetsDisplayCriteria(?UseCase $use_case): bool;

	public static function getWidgetName(): string;

	public static function getIconClass($context = null): ?string;

	public static function getLoadoutGeneratorClassStatic(): ?string;
}
