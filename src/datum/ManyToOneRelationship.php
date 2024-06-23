<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

class ManyToOneRelationship extends ForeignKeyDatum{

	public function __construct(string $name){
		parent::__construct($name, RELATIONSHIP_TYPE_MANY_TO_ONE);
	}
}
