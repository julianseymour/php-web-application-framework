<?php
namespace JulianSeymour\PHPWebApplicationFramework\paginate;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use Exception;

class Paginator extends DataStructure{

	protected $paginatedClass;
	
	public static function getDatabaseNameStatic():string{
		return "error";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$limit = new UnsignedIntegerDatum("limit", 16);
		$limit->setHumanReadableName(_("Limit"));
		$limit->setNullable(true);
		$tic = new UnsignedIntegerDatum("totalItemCount", 16);
		$total = new UnsignedIntegerDatum("totalPageCount", 16);
		$display_pg = new UnsignedIntegerDatum("pg", 16);
		$orderBy = new TextDatum("orderBy");
		$orderBy->setNullable(true);
		$orderBy->setHumanReadableName(_("Sort by"));
		$orderDirection = new StringEnumeratedDatum("orderDirection");
		$orderDirection->setValidEnumerationMap([
			DIRECTION_ASCENDING,
			DIRECTION_DESCENDING
		]);
		$orderDirection->setNullable(true);
		// $orderDirection->setChoiceGenerator(static::class);
		array_push($columns, $limit, $total, $display_pg, $orderBy, $orderDirection, $tic);
		foreach($columns as $column) {
			$column->setFlag("paginator", true);
		}
	}

	public static function getDefaultPersistenceModeStatic():int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
	
	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_LITERAL;
	}

	public static function getIdentifierNameStatic(): ?string{
		return "paginator";
	}

	public function hasLimitPerPage(){
		return $this->hasColumnValue("limit");
	}

	public function hasOrderBy(){
		return $this->hasColumnValue("orderBy");
	}

	/**
	 * this function returns just the order by term, not the direction
	 *
	 * @return string
	 */
	public function getOrderBy(){
		$f = __METHOD__;
		$print = false;
		if($this->hasOrderBy()) {
			if($print) {
				Debug::print("{$f} order by was already defined");
			}
			return $this->getColumnValue("orderBy");
		}elseif(hasInputParameter('orderBy')) {
			if($print) {
				Debug::print("{$f} order by is provided by GET");
			}
			$orderBy = getInputParameter('orderBy');
			if(! ctype_alnum($orderBy)) {
				Debug::error("{$f} orderBy is non-alphanumeric");
			}
		}else{
			if($print) {
				Debug::print("{$f} order by is not specified; defaulting to insertTimestamp");
			}
			$orderBy = "insertTimestamp";
			if(!$this->hasOrderDirection()) {
				if($print) {
					Debug::print("{$f} order direction was not defined either");
				}
				$this->setOrderDirection(DIRECTION_DESCENDING);
			}elseif($print) {
				Debug::print("{$f} order direction was defined, however");
			}
		}
		return $this->setOrderBy($orderBy);
	}

	public function hasOrderDirection(){
		return $this->hasColumnValue("orderDirection");
	}

	public function setOrderDirection($orderDirection){
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::print("{$f} setting order direction to \"{$orderDirection}\"");
		}
		return $this->setColumnValue("orderDirection", $orderDirection);
	}

	public function getOrderDirection(){
		$f = __METHOD__;
		if($this->hasOrderDirection()) {
			return $this->getColumnValue("orderDirection");
		}
		if(! hasInputParameter('orderDirection') || getInputParameter('orderDirection') !== DIRECTION_ASCENDING) {
			$orderDirection = DIRECTION_DESCENDING;
		}else{
			$orderDirection = DIRECTION_ASCENDING;
		}
		return $this->setOrderDirection($orderDirection);
	}

	public function getOrderByClause(){
		$f = __METHOD__;
		$print = false;
		$term = $this->getOrderBy();
		$orderDirection = $this->getOrderDirection();
		if($print) {
			Debug::print("{$f} ordering {$orderDirection}");
		}
		$orderBy = [
			new OrderByClause($term, $orderDirection)
		];
		if($orderBy[0]->getColumnName() !== "num") {
			array_push($orderBy, new OrderByClause("num", $orderBy[0]->getDirectionality()));
		}
		return $orderBy;
	}

	public function setOrderBy($orderBy){
		return $this->setColumnValue("orderBy", $orderBy);
	}

	/**
	 *
	 * @param SelectStatement $select
	 */
	public function paginateSelectStatement($select, $primitives = null, $args = null){
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				$count = is_array($args) ? count($args) : 0;
				Debug::print("{$f} pre-pagination select query is \"{$select}\" with type specifier \"{$primitives}\" and {$count} parameters");
			}
			if($select->hasLimit() || $select->hasOffset()) {
				Debug::error("{$f} limit and offset should not be defined until after this function has finished its business");
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			if(isset($primitives) && isset($args)) {
				$length = strlen($primitives);
				for ($i = 0; $i < $length; $i ++) {
					$type = $primitives[$i];
					if(! array_key_exists($i, $args)) {
						Debug::warning("{$f} there is no parameter {$i}");
						Debug::printArray($args);
						Debug::printStackTrace();
					}
					switch ($type) {
						case 'd':
							if($print) {
								Debug::print("{$f} parameter {$args[$i]} at position {$i} should be a double");
							}
							if(!is_float($args[$i])) {
								Debug::error("{$f} but it isn't");
							}
							break;
						case 'i':
							if($print) {
								Debug::print("{$f} parameter {$args[$i]} at position {$i} should be an integer");
							}
							if(!is_int($args[$i])) {
								Debug::error("{$f} but it isn't");
							}
							break;
						case 's':
							if($print) {
								Debug::print("{$f} parameter {$args[$i]} at position {$i} is a string");
							}
							break;
						default:
							Debug::error("{$f} there are no other data types dammit");
					}
				}
				if($print) {
					Debug::print("{$f} all parameters check out. About to execute \"{$select}\" with type specifier \"{$primitives}\" and the following parameters");
					Debug::printArray($args);
				}
				$count = $select->prepareBindExecuteGetResultCount($mysqli, $primitives, ...$args);
			}else{
				$required = $select->inferParameterCount();
				if($required > 0) {
					if(! isset($primitives)) {
						Debug::warning("{$f} type definition string is undefined");
					}
					if(! isset($args)) {
						Debug::warning("{$f} parameter array is undefined");
					}
					Debug::error("{$f} required parameter count {$required}");
				}
				$count = $select->executeGetResultCount($mysqli);
			}
			if($print) {
				Debug::print("{$f} total item count of pre-pagination query \"{$select}\" is {$count}");
			}
			$this->setTotalItemCount($count);
			if($this->hasOrderBy()) {
				if($print) {
					Debug::print("{$f} order by is defined -- generating expression");
				}
				$select->setOrderBy(...$this->getOrderByClause());
			}
			$this->initialize();
			if($this->hasLimitPerPage()) {
				$limit = $this->getLimitPerPage();
				if($limit > 0) {
					$select->setLimit($limit);
					$pg = $this->getDisplayPage();
					if($pg > 0) {
						$offset = $pg * $limit;
						$select->setOffset($offset);
					}
				}
			}else{
				Debug::error("{$f} limit is undefined");
			}
			if($print) {
				Debug::print("{$f} paginated query is \"{$select}\"");
			}
			return $select;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasTotalPageCount(){
		return $this->hasColumnValue('totalPageCount') && $this->getColumnValue("totalPageCount") > 0;
	}

	public function getTotalPageCount(){
		$f = __METHOD__;
		$print = false;
		if($this->hasTotalPageCount()) {
			if($print) {
				$total = $this->getColumnValue("totalPageCount");
				Debug::print("{$f} total page count was already defined as {$total}");
			}
			return $this->getColumnValue('totalPageCount');
		}
		$item_count = $this->getTotalItemCount();
		if($item_count === 0) {
			return 1;
		}elseif($print) {
			Debug::print("{$f} item count is {$item_count}");
		}
		$limit = $this->getLimitPerPage();
		if($print) {
			Debug::print("{$f} limit per page is {$limit}");
		}
		if($limit === 0) {
			return $this->setTotalPageCount(1);
		}
		$full_pages = $this->getFullPageCount();
		if($print) {
			Debug::print("{$f} full page count is {$full_pages}");
		}
		$total_pages = $full_pages + ($item_count % $limit === 0 ? 0 : 1);
		if($total_pages < 1) {
			Debug::error("{$f} total page count must be a positive number");
		}elseif($total_pages != floor($total_pages)) {
			Debug::error("{$f} total pages must be an integer");
		}
		return $this->setTotalPageCount($total_pages);
	}

	public static function getNextPageNumbers($start, $end){
		$f = __METHOD__;
		try{
			$print = false;
			// $start = $this->getNextPage();
			// $end = $this->getLastPage();
			if($start > $end) {
				Debug::warning("{$f} next page {$start} exceeds page limit {$end}");
				// $this->setObjectStatus(ERROR_INTEGER_OOB);
				return [];
			}elseif($start === $end) {
				if($print) {
					Debug::print("{$f} next page >= last page, returning");
				}
				return [
					$start => $start
				];
			}
			$allocated = [
				$start => $start
			];
			if($end - $start < 10) {
				$start_few = 9;
			}else{
				$start_few = 4;
			}
			for ($i = $start + 1; $i < $end && $i <= $start + $start_few; $i ++) {
				if(isset($allocated[$i])) {
					continue;
				}
				$allocated[$i] = $i;
			}
			$magnitude = 1;
			for ($endy = $end; $endy >= 10; $endy = floor($endy / 10)) {
				$magnitude *= 10;
			}
			if($print) {
				Debug::print("{$f} highest order of magnitude is {$magnitude}");
			}
			$cofactor10 = floor($end / $magnitude);
			if($print) {
				Debug::print("{$f} cofactor of 10 is {$cofactor10}");
			}
			// get $i should be the lowest order or half-order of magnitude exceeding currentpage
			$i = 10;
			$upper = $cofactor10 * $magnitude;
			$coefficient = 2;
			$tenth = floor($upper / 10);
			$fifth = floor($upper / 20);
			if($i == $tenth) {
				if($print) {
					Debug::print("{$f} lower bound is 1/10 of the magnitude -- cutting it into tenths");
				}
				for ($i = $tenth; $i <= $upper; $i += $tenth) {
					if(isset($allocated[$i])) {
						continue;
					}
					$allocated[$i] = $i;
				}
				if($print) {
					Debug::print("{$f} done breaking it into tenths");
				}
			}elseif($i == $fifth) {
				if($print) {
					Debug::print("{$f} lower bound is 1/5 the upper bound -- cutting it into fifths");
				}
				if(! isset($allocated[$i])) {
					$allocated[$i] = $i;
				}
				for ($i = $fifth; $i <= $upper; $i += $fifth) {
					if(isset($allocated[$i])) {
						continue;
					}
					$allocated[$i] = $i;
				}
				if($print) {
					Debug::print("{$f} done breaking it into fifths");
				}
			}else{
				// Debug::error("{$f} unimplemented: {$i} != {$fifth} && {$i} != {$tenth}");
				$current = $start - 1;
				while ($i < $current) {
					if($print) {
						Debug::print("{$f} {$i} is less than {$current}");
					}
					if($i * 5 < $current) {
						if($print) {
							Debug::print("{$f} {$i}*5 is less than {$current}");
						}
						$i *= 5;
						$coefficient = 5;
					}else{
						if($print) {
							Debug::print("{$f} {$i}*5 is greater than or equal to {$current}");
						}
						$coefficient = 2;
						break;
					}
					if($i * 2 < $current) {
						if($print) {
							Debug::print("{$f} {$i}*2 is less than {$current}");
						}
						$i *= 2;
						$coefficient = 2;
					}else{
						if($print) {
							Debug::print("{$f} {$i}*2 is greater than or equal to {$current}");
						}
						$coefficient = 5;
						break;
					}
				}
				if($print) {
					Debug::print("{$f} about to get a nicest round number > {$i} && < {$upper}; coefficient is {$coefficient}");
				}
				for ($round = $i; $round <= $upper; $round *= $coefficient) {
					if($print) {
						Debug::print("{$f} current round number is {$round}");
					}
					if($coefficient == 5) {
						$coefficient = 2;
					}else{
						$coefficient = 5;
					}
					if($print) {
						Debug::print("{$f} cofactor is now {$coefficient}");
					}
					if($round <= $current) {
						if($print) {
							Debug::print("{$f} {$round} is less than the display page {$current}");
						}
						// continue;
					}elseif(! isset($allocated[$round])) {
						if($print) {
							Debug::print("{$f} about to generate a link for page {$round}");
						}
						$allocated[$round] = $round;
					}else{
						if($print) {
							Debug::print("{$f} page {$round} already had its link generated");
						}
						continue;
					}
					$pre = $round * $coefficient;
					if($pre < $upper) {
						if($print) {
							Debug::print("{$f} next iteration of the loop (page {$pre}) is within the allowed range");
						}
					}elseif($print) {
						Debug::print("{$f} page {$pre} exceeds the allowed range");
					}
				}
			}
			if($print) {
				Debug::print("{$f} about to append last page link");
			}
			if(! isset($allocated[$end])) {
				$allocated[$end] = $end;
			}
			if($print) {
				Debug::print("{$f} returning the following next page numbers:");
				Debug::printArray($allocated);
			}
			return $allocated;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getLinkedPageNumbers(){
		$f = __METHOD__;
		$print = false;
		$pg = $this->getDisplayPage();
		$prev = $pg === 0 ? [] : $this->getPreviousPageNumbers($this->getPreviousPage());
		$current = [
			$pg
		];
		$last = $this->getLastPage();
		if($print) {
			Debug::print("{$f} last page number is \"{$last}\"");
		}
		$next = $pg === $last ? [] : $this->getNextPageNumbers($this->getNextPage(), $last);
		$numbers = array_merge($prev, $current, $next);
		if($print) {
			Debug::debugPrint("{$f} returning the following page numbers:");
			Debug::printArray($numbers);
		}
		return $numbers;
	}

	public static function getPreviousPageNumbers($end){
		$f = __METHOD__;
		try{
			$print = false;
			$allocated = [
				0 => 0
			];
			$coefficient = 2;
			for ($i = 10; $i < $end - 9; $i *= $coefficient) {
				if($coefficient == 5) {
					$coefficient = 2;
				}else{
					$coefficient = 5;
				}
				if(isset($allocated[$i])) {
					continue;
				}
				$allocated[$i] = $i;
			}
			$prev = $end - 1;
			$prev_start = $prev - 4;
			while ($prev_start < 1) {
				$prev_start ++;
			}
			for ($i = $prev_start; $i <= $prev; $i ++) {
				if(isset($allocated[$i])) {
					continue;
				}
				$allocated[$i] = $i;
			}
			if($print) {
				Debug::debugPrint("{$f} returning the following previous page numbers:");
				Debug::printArray($allocated);
			}
			return $allocated;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setTotalPageCount($total_pages){
		$f = __METHOD__;
		$print = false;
		if($total_pages < 1) {
			Debug::error("{$f} total pages must be a positive number");
		}elseif(floor($total_pages) != $total_pages) {
			Debug::error("{$f} fuck!");
		}elseif($print) {
			Debug::print("{$f} setting total page count to {$total_pages}");
		}
		return $this->setColumnValue('totalPageCount', $total_pages);
	}

	public function hasTotalItemCount()
	{
		return $this->hasColumnValue("totalItemCount");
	}

	public function getTotalItemCount(){
		$f = __METHOD__;
		if(!$this->hasTotalItemCount()) {
			$did = $this->getDebugId();
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} this should have been calculated when setting the paginated query. Debug ID is {$did}, declared {$decl}");
		}
		return $this->getColumnValue("totalItemCount");
	}

	public function setTotalItemCount($count){
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::printStackTraceNoExit("{$f} setting total item count to {$count}");
		}
		return $this->setColumnValue("totalItemCount", $count);
	}

	public function getFullPageCount(){
		$f = __METHOD__;
		$limit = $this->getLimitPerPage();
		if($limit === 0) {
			$limit = 1;
		}
		return floor($this->getTotalItemCount() / $limit);
	}

	public function setDisplayPage($pg){
		$f = __METHOD__;
		$last = $this->getLastPage();
		if(isset($last) && $pg > $last) {
			Debug::error("{$f} display page ({$pg}) exceeds last page number ({$last})");
		}
		return $this->setColumnValue('pg', $pg);
	}

	public function setLimitPerPage($limit){
		return $this->setColumnValue('limit', $limit);
	}

	public function hasDisplayPage(){
		return $this->hasColumnValue('pg');
	}

	public function getDisplayPage(){
		return $this->getColumnValue('pg');
	}

	public function setPaginatedClass($pc)
	{
		return $this->paginatedClass = $pc;
	}

	public function hasPaginatedClass(){
		return isset($this->paginatedClass);
	}

	public function getPaginatedClass(){
		$f = __METHOD__;
		if(!$this->hasPaginatedClass()) {
			Debug::error("{$f} paginated class is undefined");
		}
		return $this->paginatedClass;
	}

	/**
	 *
	 * @return int;
	 */
	public function initialize(){
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} entered");
			}
			// $this->getLimitPerPage();
			if(hasInputParameter('limit') && intval(getInputParameter('limit') > 0)) {
				$this->setLimitPerPage(intval(getInputParameter('limit')));
			}elseif($print) {
				Debug::print("{$f} limit per page is undefined in GET");
			}
			$this->getFullPageCount();
			$this->getTotalPageCount();
			// display page //$this->initializeDisplayPage();
			if(hasInputParameter('jump')) {
				$pg = intval(getInputParameter('jump'));
				if($print) {
					Debug::print("{$f} jumping to page #{$pg}");
				}
			}elseif(hasInputParameter('pg')) {
				$pg = intval(getInputParameter('pg'));
				if($print) {
					Debug::print("{$f} page # is {$pg}");
				}
			}elseif(hasInputParameter('tx_id')) {
				$tx_id = intval(getInputParameter('tx_id'));
				if($print) {
					Debug::print("{$f} transaction ID is \"{$tx_id}\"");
				}
				$count = $this->getTotalItemCount();
				$reversed = $count - $tx_id;
				if($reversed < 0) {
					Debug::error("{$f} negative variable \"reversed\" ({$reversed})");
				}
				$limit = $this->getLimitPerPage();
				$adjusted = ($reversed % $limit); // + 1;
				$perpage = $limit;
				$pg = floor($adjusted / $perpage) + floor($reversed / $limit); // *$pages_per_volume;
				if($pg < 0) {
					$arr = [
						'tx_id' => $tx_id,
						'count' => $count,
						'reversed' => $reversed,
						'limit' => $limit,
						'adjusted' => $adjusted,
						'perpage' => $perpage,
						// 'pages_per_volume' => $pages_per_volume,
						'pg' => $pg
					];
					Debug::printArray($arr);
					Debug::error("{$f} negative page number");
				}
			}else{
				if($print) {
					Debug::print("{$f} nothing in GET parameters can be used to extrapolate the display page number");
				}
				$pg = 0; // $this->getStartingPage();
			}
			if($print) {
				Debug::print("{$f} about to assign display page number {$pg}");
			}
			if($pg < 0) {
				Debug::warning("{$f} negative page number");
				return $this->setObjectStatus(ERROR_INTEGER_OOB);
			}
			$this->setDisplayPage($pg);
			$this->getNextPage();
			$this->getPreviousPage();
			$this->getOrderBy();
			// $this->setFlag("initialized", true);
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getLastPage():int{
		return $this->getTotalPageCount() - 1;
	}

	public function getPreviousPage():int{
		return $this->getDisplayPage() - 1;
	}

	public function getLimitPerPage(){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasLimitPerPage()) {
			return $this->setLimitPerPage($this->getTotalItemCount());
		}elseif($print) {
			$limit = $this->getColumnValue('limit');
			Debug::print("{$f} limit is {$limit}");
		}
		return $this->getColumnValue('limit');
	}

	public function getNextPage(){
		return $this->getDisplayPage() + 1;
	}

	public static function getPrettyClassName():string{
		return static::class;
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPhylumName(): string{
		return "paginator";
	}

	public static function getPrettyClassNames():string{
		return _("Paginators");
	}

	public function getPageLinkHTTPQueryParameters($pg){
		return [
			"pg" => $pg,
			'limit' => $this->getLimitPerPage(),
			'orderBy' => $this->getOrderBy(),
			'orderDirection' => $this->getOrderDirection(),
			"pg_state" => "loaded"
		];
	}
}
