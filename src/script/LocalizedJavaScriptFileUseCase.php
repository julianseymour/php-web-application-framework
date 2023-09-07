<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

abstract class LocalizedJavaScriptFileUseCase extends JavaScriptFileUseCase{
	
	public function getActionAttribute(): ?string{
		return "/script";
	}
	
	public function getUriSegmentParameterMap():?array{
		return [
			"action",
			"locale",
			"filename"
		];
	}
}