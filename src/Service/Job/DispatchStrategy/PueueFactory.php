<?php
namespace PueueJobDispatcher\Service\Job\DispatchStrategy;


use Omeka\Job\DispatchStrategy\PhpCli;
use Omeka\Job\DispatchStrategy\StrategyInterface;
use Omeka\Settings\Settings;
use PueueJobDispatcher\Job\DispatchStrategy\Pueue;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PueueFactory implements FactoryInterface
{
    /**
     * Create the Pueue/PhpCli strategy service.
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Pueue
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StrategyInterface
    {
        /** @var Settings $settings */
        $settings = $container->get('Omeka\Settings');

        if ($settings->get('pueue_enabled')) {
            $viewHelpers = $container->get('ViewHelperManager');
            $basePathHelper = $viewHelpers->get('BasePath');
            $serverUrlHelper = $viewHelpers->get('ServerUrl');

            $config = $container->get('Config');

            $phpPath = null;
            if (isset($config['cli']['phpcli_path']) && $config['cli']['phpcli_path']) {
                $phpPath = $config['cli']['phpcli_path'];
            }
            $pueuePath = $settings->get('pueue_path');
            $pueueGroup = $settings->get('pueue_group');
            return new Pueue($container->get('Omeka\Cli'), $container->get('Omeka\Logger'), $basePathHelper(),
                $serverUrlHelper(), $phpPath, $pueuePath, $pueueGroup);
        } else {
            return $container->get(PhpCli::class);
        }

    }
}
