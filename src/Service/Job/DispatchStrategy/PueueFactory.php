<?php
namespace PueueJobDispatcher\Service\Job\DispatchStrategy;


use PueueJobDispatcher\Job\DispatchStrategy\Pueue;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PueueFactory implements FactoryInterface
{
    /**
     * Create the PhpCli strategy service.
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Pueue
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Pueue
    {
        $viewHelpers = $container->get('ViewHelperManager');
        $basePathHelper = $viewHelpers->get('BasePath');
        $serverUrlHelper = $viewHelpers->get('ServerUrl');

        $config = $container->get('Config');
        $settings = $container->get('Omeka\Settings');
        $phpPath = null;
        if (isset($config['cli']['phpcli_path']) && $config['cli']['phpcli_path']) {
            $phpPath = $config['cli']['phpcli_path'];
        }
        $pueuePath = $settings->get('pueue_path');
        $pueueGroup = $settings->get('pueue_group');
        return new Pueue($container->get('Omeka\Cli'), $container->get('Omeka\Logger'), $basePathHelper(),
            $serverUrlHelper(), $phpPath, $pueuePath, $pueueGroup);
    }
}
