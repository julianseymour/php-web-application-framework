<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;

class EncryptionComponentDatum extends Base64Datum
{

	protected $originalDatumIndex;

	public function getOriginalDatumIndex()
	{
		return $this->originalDatumIndex;
	}

	public function setOriginalDatumIndex($index)
	{
		return $this->originalDatumIndex = $index;
	}

	public function getOriginalDatum()
	{
		return $this->getDataStructure()->getColumn($this->getOriginalDatumIndex());
	}
}
