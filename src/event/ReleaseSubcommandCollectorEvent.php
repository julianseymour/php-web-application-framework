<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

class ReleaseSubcommandCollectorEvent extends ReleaseEvent{
	
	public function __construct($scc=null, bool $deallocate=false, ?array $properties=null){
		if($properties === null){
			$properties = [];
		}
		if($scc !== null){
			$properties['subcommandCollector'] = $scc;
		}
		parent::__construct(EVENT_RELEASE_SUBCOMMAND_COLLECTOR, $deallocate, $properties);
	}
}
