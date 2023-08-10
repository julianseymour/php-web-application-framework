<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

/**
 * forms that implement this interface will be treated differently in AjaxForm->subIndexSubordinateInputs
 *
 * @author j
 *        
 */
interface RepeatingFormInterface
{

	function getLastChildFlag(): bool;

	function setLastChildFlag($value = true): bool;

	static function getNewFormOption(): bool;

	function getContext();

	function getFlag(string $name): bool;

	function getAllocationMode(): int;

	function getSuperiorForm(): AjaxForm;

	function getSuperiorFormIndex();

	function getTemplateFlag(): bool;

	function setFlag(string $name, bool $value = true): bool;
}
