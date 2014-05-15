<?php

namespace Kdyby\SessionPanel\DI;

use Nette;



if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (!class_exists('Nette\PhpGenerator\ClassType')) {
	class_alias('Nette\Utils\PhpGenerator\ClassType', 'Nette\PhpGenerator\ClassType');	
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']);
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

if (!class_exists('Tracy\Debugger')) {
	class_alias('Nette\Diagnostics\Debugger', 'Tracy\Debugger');
}

if (!class_exists('Tracy\Dumper')) {
	class_alias('Nette\Diagnostics\Dumper', 'Tracy\Dumper');
}

if (!class_exists('Tracy\IBarPanel')) {
	class_alias('Nette\Diagnostics\IBarPanel', 'Tracy\IBarPanel');
}

if (!class_exists('Nette\Utils\DateTime')) {
	class_alias('Nette\Utils\DateTime', 'Nette\DateTime');
}

if (!class_exists('Nette\Templating\Helpers')) {
	class_alias('Latte\Runtime\Filters', 'Nette\Templating\Helpers');
}

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SessionPanelExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setClass('Kdyby\SessionPanel\Diagnostics\SessionPanel');
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$class->methods['initialize']->addBody($builder->formatPhp(
				'Nette\Diagnostics\Debugger::' . (method_exists('Nette\Diagnostics\Debugger', 'getBar') ? 'getBar()' : '$bar') . '->addPanel(?);',
				Nette\DI\Compiler::filterArguments(array(new Nette\DI\Statement($this->prefix('@panel'))))
			));
		}
	}

	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('debugger.session', new SessionPanelExtension());
		};
	}

}
