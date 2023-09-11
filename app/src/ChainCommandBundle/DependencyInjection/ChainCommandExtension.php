<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ChainCommandExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $commandChainsConfig = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yaml');

        if (isset($commandChainsConfig['chain_commands'])) {
            $this->loadChainConfiguration($container, $commandChainsConfig);
        }
    }

    protected function loadChainConfiguration(ContainerBuilder $container, array $commandChainsConfig): void
    {
        $definition = $container->getDefinition('chain_command.manager');
        foreach ($commandChainsConfig['chain_commands'] as $chainConfig) {
            $rootCommand = $chainConfig['root_command'];
            $memberCommands = $chainConfig['member_commands'];

            $definition->addMethodCall('addChain', [
                $rootCommand,
                $memberCommands
            ]);
        }
    }
}