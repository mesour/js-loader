<?php

namespace JsApplication\Templating;

/**
 * Description of TemplateEngine
 *
 * @author mesour <matous.nemec@mesour.com>
 */
class TemplateEngine{
	
	private $content = '';
	
	/**
	 * Parse template content
	 * 
	 * @param String $content
	 * @param Boolean $children
	 * @return String
	 */
	public function parse($content, $children = FALSE) {
		$this->content = ($children ? '' : $this->parseParameters()) . $content;
		$this->parseVariables();
		$this->parseConditions();
		$this->parseForeach();
		$this->parseIncludes();
		return $this->content;
	}
	
	private function parseParameters() {
		$output = "<?php foreach(\$template->getParameters() as \$name => \$value) {\n";
		$output .= "\t$\$name = \$value;\n";
		$output .= "} ?>\n";
		return $output;
	}
	
	private function parseVariables() {
		$this->content = preg_replace(array(
		    '/\{=([^\}]+)\}/', // call and echo return of your function {=my_function('test_value')}
		    '/\{!(\$[^\}]+)\}/', // not escape {!$variable}
		    '/\{(\$[^\}]+)\}/', // escape {$variable}
		    '/\{var ([^\}]+)\}/', // set php variable {var $variable = 'my-content'}
		), array(
		    '<?php echo $1; ?>',
		    '<?php echo $1; ?>',
		    '<?php echo htmlspecialchars($1); ?>',
		    '<?php $1; ?>'
		), $this->content);
	}
	
	private function parseConditions() {
		$this->content = preg_replace(array(
		    '/\{if ([^\}]+)\}/', // {if $x === $a}
		    '/\{elseif ([^\}]+)\}/', // {elseif $x === $b}
		    '/\{else\}/', // {else}
		    '#\{/if\}#', // {/if}
		), array(
		    '<?php if($1) { ?>',
		    '<?php } elseif($1) { ?>',
		    '<?php } else { ?>',
		    '<?php } ?>'
		), $this->content);
	}
	
	private function parseForeach() {
		$this->content = preg_replace(array(
		    '/\{foreach ([^\}]+)\}/', // {foreach $var as $x => $a}
		    '#\{/foreach\}#', // {/foreach}
		), array(
		    '<?php foreach($1) { ?>',
		    '<?php } ?>'
		), $this->content);
	}
	
	/**
	 * {include myFile.tpl}
	 */
	private function parseIncludes() {
		$this->content = preg_replace_callback('/\{include ([^\}]+)\}/', array($this, 'applyInclude'), $this->content);
	}
	
	private function applyInclude($matches) {
		$output = '<?php $_included[] = new \JsApplication\Templating\Template($template->getEngine(), TRUE);';
		$output .= '$_included[count($_included)-1]->setTemplateFile('.$matches[1].');';
		$output .= '$_included[count($_included)-1]->setCacheFile($loader->getCacheDir() . md5('.$matches[1].'));';
		$output .= '$_included[count($_included)-1]->render();';
		$output .= 'include $_included[count($_included)-1]->getCacheFile();';
		return $output . ' ?>';
	}
	
}