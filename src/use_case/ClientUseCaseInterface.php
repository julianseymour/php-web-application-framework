<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;

interface ClientUseCaseInterface extends JavaScriptCounterpartInterface
{

	function getClientUseCaseName(): ?string;
}