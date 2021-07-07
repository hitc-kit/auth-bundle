<?php

namespace HitcKit\AuthBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class HitcKitAuthExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * @inheritDoc
     * @throws ParseException
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $path = __DIR__.'/../Resources/config';

        if (isset($bundles['SecurityBundle'])) {
            $config = Yaml::parseFile($path.'/security.yaml');
            $container->loadFromExtension('security', $config);
        }

        if (isset($bundles['HitcKitAdminBundle'])) {
            $container->prependExtensionConfig('hitc_kit_admin', [
                'templates' => [
                    'menu_user' => '@HitcKitAuth/menu_user.html.twig'
                ]
            ]);
        }
    }
}
