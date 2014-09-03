<?php include $files_content; ?>

{foreach $created_modules as $name => $options}
	{var $options_file = $loader->getFilePath('core/options')}
	{if is_file($options_file)}{include $options_file}{/if}
	var {$options['js_var_name']} = Application.init('{$name}');
	{foreach $options['plugins'] as $plugin_name => $plugin_options}
		{var $plugin_options_file = $loader->getFilePath($plugin_options['file'].'_init')}
		{if is_file($plugin_options_file)}{include $plugin_options_file}{/if}
	{/foreach}
	{foreach $options['components'] as $component_name => $component_options}
		{var $component_options_file = $loader->getFilePath($component_options['file'].'_init')}
		{if is_file($component_options_file)}{include $component_options_file}{/if}
	{/foreach}
{/foreach}