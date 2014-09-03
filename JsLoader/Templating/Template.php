<?php
/**
 * This file is part of JsApplication
 * 
 * @copyright 2014 Matous Nemec
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 * @license http://www.opensource.org/licenses/MIT MIT
 */

namespace JsApplication\Templating;

/**
 * Template
 *
 * @package JsApplication
 * @author mesour <matous.nemec@mesour.com>
 * @version 0.1
 */
class Template {
	
	private $parameters = array();
	
	private $cache_file;
	
	private $template_file;
	
	private $is_children = FALSE;
	
	/**
	 *
	 * @var \JsApplication\TemplateEngine
	 */
	private $engine;
	
	/**
	 * Create instance
	 * 
	 * @param \JsApplication\Templating\TemplateEngine $engine
	 * @param Boolean $children
	 */
	public function __construct($engine = NULL, $children = FALSE) {
		if(is_null($engine) === FALSE) {
			$this->setEngine($engine);
		}
		$this->is_children = $children;
	}
	
	/**
	 * Set template engine
	 * 
	 * @param \JsApplication\Templating\TemplateEngine $engine
	 * @throws Template_Exception
	 */
	public function setEngine($engine) {
		if($engine === 'JsApplication\Templating\TemplateEngine') {
			$this->engine = new TemplateEngine;
		} elseif($engine instanceof TemplateEngine) {
			$this->engine = $engine;
		} else {
			throw new Template_Exception('Engine must be instance of JsApplication\Templating\TemplateEngine or string "JsApplication\Templating\TemplateEngine".');
		}
	}
	
	/**
	 * Get template engine instance
	 * 
	 * @return \JsApplication\TemplateEngine
	 */
	public function getEngine() {
		return $this->engine;
	}
	
	/**
	 * Set cache filename
	 * 
	 * @param String $cache_file
	 */
	public function setCacheFile($cache_file) {
		$this->cache_file = $cache_file;
	}
	
	/**
	 * Get cache filename
	 * 
	 * @return String
	 */
	public function getCacheFile() {
		return $this->cache_file;
	}
	
	/**
	 * Return template parameters as Array
	 * 
	 * @return Array
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * Set template parameters
	 * 
	 * @param Array $parameters
	 */
	public function setParameters(array $parameters) {
		$this->parameters = $parameters;
	}
	
	/**
	 * Get template filename
	 * 
	 * @return String
	 */
	public function getTemplateFile() {
		return $this->template_file;
	}
	
	/**
	 * Set template file name
	 * 
	 * @param String $template_file
	 */
	public function setTemplateFile($template_file) {
		$this->template_file = $template_file;
	}
	
	/**
	 * Render template
	 * 
	 * @return String
	 * @throws Template_Exception
	 */
	public function render() {
		if(!file_exists($this->template_file)) {
			throw new Template_Exception('Template file '.$this->template_file.' does not exist.');
		}

		if(file_exists($this->cache_file) && filemtime($this->cache_file) > filemtime($this->template_file)) {
			return file_get_contents($this->cache_file);
		} else {
			$content = $this->applyEngine();
			file_put_contents($this->cache_file, $content);
		}
		return $content;
	}
	
	/**
	 * Apply template engine
	 * 
	 * @return String
	 * @throws Template_Exception
	 */
	public function applyEngine() {
		if(!$this->engine instanceof TemplateEngine) {
			throw new Template_Exception('Engine is required. use default $this->setEngine("JsApplication\Templating\TemplateEngine")');
		}
		return $this->engine->parse(file_get_contents($this->template_file), $this->is_children);
	}
	
	/**
	 * Get template parameter
	 * 
	 * @param String $name
	 * @return Mixed
	 * @throws Template_Exception
	 */
	public function __get($name) {
		if(!isset($this->parameters[$name])) {
			throw new Template_Exception('Template variable ' . $name . ' does not exist.');
		}
		return $this->parameters[$name];
	}
	
	/**
	 * Set template parameter
	 * 
	 * @param String $name
	 * @param Mixed $value
	 */
	public function __set($name, $value) {
		$this->parameters[$name] = $value;
	}
	
	/**
	 * Unset tempalte parameter
	 * 
	 * @param String $name
	 */
	public function __unset($name) {
		unset($this->parameters[$name]);
	}
	
	/**
	 * Check if parameter is set
	 * 
	 * @param String $name
	 * @return Boolean
	 */
	public function __isset($name) {
		return isset($this->parameters[$name]);
	}
	
	/**
	 * Render template
	 * 
	 * @return String
	 */
	public function __toString() {
		return $this->render();
	}
	
}

/**
 * Template exception
 */
class Template_Exception extends \Exception {
	
}