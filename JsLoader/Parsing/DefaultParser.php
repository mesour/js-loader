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
 * Default parser
 *
 * @package JsApplication
 * @author mesour <matous.nemec@mesour.com>
 * @version 0.1
 */
class DefaultParser implements IParser {
	
	/**
	 * Parse JS code
	 * 
	 * @param String $content
	 * @return String
	 */
	public function parse($content) {
		// remove comments
		$content = preg_replace('/\/\*([\s\S]*?)\*\/|\/\/(.+)\n/', '', $content);
		// remove tabs, spaces, newlines, etc.
		return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $content);
	}
	
}
