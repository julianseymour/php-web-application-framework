<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use function JulianSeymour\PHPWebApplicationFramework\php2string;
use function JulianSeymour\PHPWebApplicationFramework\request;

abstract class FramePhpFileUseCase extends UseCase
{

	public abstract function getPhpFilename(): string;

	public function getActionAttribute(): ?string
	{
		return request()->getRequestURI();
	}

	public function getPageContent(): ?array
	{
		return [
			php2string($this->getPhpFilename())
		];
	}
}