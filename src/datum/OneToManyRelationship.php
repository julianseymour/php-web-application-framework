<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;

class OneToManyRelationship extends KeyListDatum{

	public function __construct(string $name){
		parent::__construct($name, RELATIONSHIP_TYPE_ONE_TO_MANY);
	}
}
