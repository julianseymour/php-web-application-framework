<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\input\choice\DataListElement;

trait ListAttributeTrait
{

	protected $datalist;

	public function getListAttribute()
	{
		return $this->getAttribute("list");
	}

	public function hasListAttribute()
	{
		return $this->hasAttribute("list");
	}

	public function setListAttribute($value)
	{
		return $this->setAttribute("list", $value);
	}

	public function setDataList(DataListElement $list)
	{
		$this->setListAttribute($list->getIdAttribute());
		return $this->datalist = $list;
	}
}