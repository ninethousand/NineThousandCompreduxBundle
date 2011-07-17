<?php

namespace NineThousand\Bundle\NineThousandCompreduxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;

use NineThousand\Bundle\NineThousandCompreduxBundle\NineThousandCompreduxBundleException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NineThousandCompreduxExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        //$config = $processor->processConfiguration($configuration, $configs);
        
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('compredux.xml');
        
        $config = $this->mergeExternalConfig($configs);
        $this->_init($config, $container);
    }
    
    private function mergeExternalConfig($config)
    {
        $mergedConfig = array();

        foreach ($config as $cnf)
        {
            $mergedConfig = array_merge($mergedConfig, $cnf);
        }
        
        return $mergedConfig;
    }
    
    private function _init($config, $container)
    {
        if (isset($config['class'])) {
            $container->setParameter('compredux.client.class', $configs['class']);
        }

        $cacheDir = $container->getParameter('kernel.root_dir') . '/cache/compredux';
        $filesystem = $container->get('compredux.filesystem');

        //create controllers for defined proxies
        foreach ($config['proxies'] as $name => $proxy)
        {
            $proxy['home_dir'] = $container->getParameter('kernel.root_dir') . '/cache/compredux/'.$name;
            try {
                $filesystem->mkdir($proxy['home_dir'].'/', 0777);
            } catch (NineThousandCompreduxBundleException $e) {
                echo 'Compredux exception: ',  $e->getMessage(), "\n";
            }

            $container->setDefinition('compredux.'.$name, new Definition(
                $container->getParameter('compredux.client.class'),
                array($proxy)
            ));
        }
        
    }
    
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }
}
