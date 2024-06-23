<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

interface InputlikeInterface
{

	public function getForm();

	public function hasForm(): bool;

	public function setForm(?FormElement $form): ?FormElement;

	public function setNameAttribute($name);

	public function getNameAttribute();

	public function hasNameAttribute(): bool;

	public function getColumnName();

	public function subindexNameAttribute($name);

	public function configure(?AjaxForm $form=null): int;

	public function processArray(array $arr): int;
}
