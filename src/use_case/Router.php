<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use JulianSeymour\PHPWebApplicationFramework\app\Request;

abstract class Router
{

	public abstract function getUseCase(Request $request): UseCase;
}
