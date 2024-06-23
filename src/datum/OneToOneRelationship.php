<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

class OneToOneRelationship extends ForeignKeyDatum{

	public function __construct(string $name){
		parent::__construct($name, RELATIONSHIP_TYPE_ONE_TO_ONE);
	}
}
