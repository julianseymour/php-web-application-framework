<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ReferenceElementIdTrait
{

	protected $referenceElementId;

	public function setReferenceElementId($id)
	{
		$f = __METHOD__; //"ReferenceElementIdColumnTrait(".static::getShortClass().")->setReferenceElementId()";
		if ($id == null) {
			unset($this->referenceElementId);
			return null;
		}
		return $this->referenceElementId = $id;
	}

	public function hasReferenceElementId()
	{
		return isset($this->referenceElementId);
	}

	public function getReferenceElementId()
	{
		$f = __METHOD__; //"ReferenceElementIdColumnTrait(".static::getShortClass().")->getReferenceElementId()";
		if (! $this->hasReferenceElementId()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} insertion target ID is undefined; declared {$decl}");
		}
		return $this->referenceElementId;
	}
}
