<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;

class ManyToManyRelationship extends KeyListDatum{

	public function __construct(string $name){
		parent::__construct($name, RELATIONSHIP_TYPE_MANY_TO_MANY);
	}
}
