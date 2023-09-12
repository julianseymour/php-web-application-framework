<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use JulianSeymour\PHPWebApplicationFramework\element\ElementTagTrait;

class LoadXMLStatement extends LoadStatement
{

	use ElementTagTrait;

	public function getQueryStatementString()
	{
		// LOAD XML
		$string = "load XML " . parent::getQueryStatementString();
		// [CHARACTER SET charset_name]
		if($this->hasCharacterSet()) {
			$string .= " character set " . $this->getCharacterSet();
		}
		// [ROWS IDENTIFIED BY '<tagname>']
		if($this->hasElementTag()) {
			$tag = escape_quotes($this->getElementTag(), QUOTE_STYLE_SINGLE);
			$string .= " rows identified by '<{$tag}>'";
			unset($tag);
		}
		// [IGNORE number {LINES | ROWS}]
		if($this->hasIgnoreRows()) {
			$string .= " ignore " . $this->getIgnoreRows() . " rows";
		}
		// [(field_name_or_user_var [, field_name_or_user_var] ...)]
		if($this->hasColumnNames()) {
			$string .= " (" . implode_back_quotes(',', $this->getColumnNames()) . ")";
		}
		// [SET col_name={expr | DEFAULT} [, col_name={expr | DEFAULT}] ...]
		if($this->hasExpressions()) {
			$string .= " set " . implode(',', $this->getExpressions());
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->tag);
	}
}
