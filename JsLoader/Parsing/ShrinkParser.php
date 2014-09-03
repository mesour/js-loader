<?php
/**
 * This file is part of JsApplication
 * 
 * @copyright 2014 Matous Nemec
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 * @license http://www.opensource.org/licenses/MIT MIT
 */

namespace JsApplication\Parsing;

/**
 * Shrink parser
 *
 * @package JsApplication
 * @author mesour <matous.nemec@mesour.com>
 * @version 0.1
 */
class ShrinkParser implements IParser {
	
	/**
	 * Parse JS code
	 * 
	 * @param String $content
	 * @return String
	 */
	public function parse($content) {
		require_once __DIR__ . '/JavaScriptPacker.php';
		$packer = new \JavaScriptPacker($content, 'None', TRUE, TRUE);
		return $packer->pack();
	}
	
}
