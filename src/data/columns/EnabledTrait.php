<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;

trait EnabledTrait
{

	public function setEnabled($e)
	{
		return $this->setColumnValue('enabled', $e);
	}

	public static function getIsEnabledDatum($default = false)
	{
		$enable = new BooleanDatum("enabled");
		$enable->setDefaultValue($default);
		$enable->setHumanReadableName(_("Enabled"));
		$enable->setAdminInterfaceFlag(true);
		return $enable;
	}

	public final function isEnabled()
	{
		return $this->getColumnValue('enabled');
	}

	public function disable()
	{
		$this->setEnabled(false);
		return $this;
	}

	public function enable()
	{
		$this->setEnabled(true);
		return $this;
	}

	public function isDisabled()
	{
		return ! $this->isEnabled();
	}
}
