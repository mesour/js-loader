<?php
/**
 * This file is part of JsApplication
 * 
 * @copyright 2014 Matous Nemec
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 * @license http://www.opensource.org/licenses/MIT MIT
 */

namespace JsApplication;

/**
 * Loader for Javascript framework
 *
 * @package JsApplication
 * @author mesour <matous.nemec@mesour.com>
 * @version 0.1
 */
class Loader {
	
	private $cache_dir;
	
	private $base_dir = array();
	
	private $files = array();
	
	private $modules = array();
	
	private $plugins = array();
	
	private $created_modules = array();
	
	private $components = array();
	
	/**
	 *
	 * @var Parsing\IParser
	 */
	private $parser;
	
	/**
	 *
	 * @var Templating\Template
	 */
	private $template;
	
	/**
	 * Create instance
	 * 
	 * @param Array|String $base_dir Can use many base dirs in array. Correct for file exists will be first searched path in array
	 * @param String $cache_dir
	 */
	public function __construct($base_dir, $cache_dir) {
		if(is_array($base_dir)) {
			$this->base_dir = $base_dir;
		} else {
			$this->base_dir[] = $base_dir;
		}
		$this->cache_dir = $cache_dir;
		$this->template = new Templating\Template("JsApplication\Templating\TemplateEngine");
	}
	
	/**
	 * Load file for example
	 * 
	 * @param Array|String $file
	 */
	public function load($file) {
		if(is_array($file)) {
			foreach($file as $f) {
				$this->files[$f] = $f;
			}
		} else {
			$this->files[$file] = $file;
		}
	}
	
	/**
	 * Load module file and save module options
	 * 
	 * @param String $module_name
	 * @param String $file
	 * @param Array $options
	 */
	public function loadModule($module_name, $file, array $options = array()) {
		$this->load($file);
		$this->modules[$module_name] = array(
		    'file' => $file,
		    'options' => $options
		);
	}
	
	/**
	 * Load plugin file and save plugin options
	 * 
	 * @param String $plugin_name
	 * @param String $file
	 * @param Array $options
	 */
	public function loadPlugin($plugin_name, $file, array $options = array()) {
		$this->load($file);
		$this->plugins[$plugin_name] = array(
		    'file' => $file,
		    'options' => $options
		);
	}
	
	/**
	 * Load component file and save plugin options
	 * 
	 * @param String $component_name Component name in JS
	 * @param String $file
	 * @param String $js_property_name Name for JS module property
	 * @param Array $options
	 */
	public function loadComponent($component_name, $file, $js_property_name, array $options = array()) {
		$this->load($file);
		$this->components[$component_name] = array(
		    'file' => $file,
		    'js_property' => $js_property_name,
		    'options' => $options
		);
	}
	
	/**
	 * Create instance of module. 
	 * 
	 * @example $this->createModule('Front', 'App'); -->> var App = Application.init('Front');
	 * @param String $name
	 * @param String $js_variable_name
	 */
	public function createModule($name, $js_variable_name) {
		$this->created_modules[$name] = array(
		    'js_var_name' => $js_variable_name,
		    'module' => $name,
		    'options' => $this->modules[$name]['options'],
		    'plugins' => $this->plugins,
		    'components' => $this->components
		);
		$this->plugins = array();
		$this->components = array();
	}
	
	/**
	 * Get cache dir name
	 * 
	 * @return String
	 */
	public function getCacheDir() {
		return $this->cache_dir;
	}
	
	/**
	 * Get template instance
	 * 
	 * @return Templating\Template
	 */
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	 * Set parser for JS files
	 * 
	 * @param \JsApplication\Parsing\IParser $parser
	 */
	public function setParser(Parsing\IParser $parser) {
		$this->parser = $parser;
	}
	
	/**
	 * Create core script, bes way is using die; or exit; after calling this method
	 */
	public function createCoreScript() {
		$template = $this->template;
		$this->createCoreFile();
		include $this->getJsSrc();
	}
	
	/**
	 * Get file path
	 * 
	 * @param String $file
	 * @return String
	 */
	public function getFilePath($file) {
		foreach($this->base_dir as $dir) {
			if(file_exists($dir . $file . '.js')) {
				return $dir . $file . '.js';
			}
		}
		return $file;
	}
	
	private function refreshCoreCache() {
		$files_content = '';
		foreach($this->files as $file) {
			$files_content .= $this->getFileContent($file);
		}
		file_put_contents($this->getFilesCacheFile(), $this->parseContent($files_content));
	}
	
	private function getFilesCacheFile() {
		return $this->cache_dir . md5(json_encode($this->files));
	}
	
	private function cacheCore() {
		$cache_file = $this->getFilesCacheFile();
		if(file_exists($cache_file)) {
			$last_file_time = 0;
			foreach($this->files as $file) {
				$current_time = filemtime($this->getFilePath($file));
				if($current_time > $last_file_time) {
					$last_file_time = $current_time;
				}
			}
			if($last_file_time > filemtime($cache_file)) {
				$this->refreshCoreCache();
			}
		} else {
			$this->refreshCoreCache();
		}
		return $cache_file;
	}
	
	private function createCoreFile() {
		$this->template->files_content = $this->cacheCore();
		$this->template->modules = $this->modules;
		$this->template->created_modules = $this->created_modules;
		$this->template->loader = $this;
		
		$this->template->setTemplateFile(__DIR__ . '/base.tpl');
		$this->template->setCacheFile($this->getJsSrc());
		$this->template->render();
	}
	
	private function parseContent($content) {
		if($this->parser instanceof Parsing\IParser) {
			return $this->parser->parse($content);
		} else {
			return $content;
		}
	}
	
	private function getJsSrc() {
		return $this->cache_dir . $this->getCurrentFileName() . '.pjs';
	}
	
	private function getFileContent($file) {
		return file_get_contents($this->getFilePath($file));
	}
	
	private function getCurrentFileName() {
		$modules = $this->created_modules;
		foreach($modules as $id => $module) {
			foreach($module['plugins'] as $name => $plugin) {
				unset($modules[$id]['plugins'][$name]['options']);
			}
		}
		return md5(json_encode($this->files) . json_encode($modules));
	}
	
}
