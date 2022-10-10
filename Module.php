<?php
namespace PueueJobDispatcher;

use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Settings\Settings;
use Omeka\Stdlib\Cli;
use PueueJobDispatcher\Form\ConfigForm;
use Laminas\Config\Factory;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use Omeka\Mvc\Controller\Plugin\Messenger;
use Generic\AbstractModule;
use PueueJobDispatcher\Job\DispatchStrategy\Pueue;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    private array $config = [];

    protected array $dependencies = [];

    /**
     * @param ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
//        Omeka\Job\DispatchStrategy

    }

    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        // overwrite Dispatchstrategy alias to Pueue?
        $services = $this->getServiceLocator();
        /** @var $settings Settings */
        $settings = $services->get('Omeka\Settings');
        if ( $settings->get('pueue_enabled') ) {
            $services->setAlias('Omeka\Job\DispatchStrategy', Pueue::class);
        }
    }

    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }

        // Load our configuration.
        $this->config = Factory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );

        return $this->config;
    }

    /**
     * Get Configuration Form
     *
     * @param PhpRenderer $renderer
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        /** @var array $config */
        $config = $services->get('Config');
        /** @var Settings $settings */
        $settings = $services->get('Omeka\Settings');
        /** @var Cli $cli */
        $cli = $services->get('Omeka\Cli');
        $view = $renderer;

        $plugins = $services->get('ControllerPluginManager');
        /** @var Messenger $messenger */
        $messenger = $plugins->get('messenger');

        // prepare form values
        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($defaultSettings as $name => $value) {
            $data[$name] = $settings->get($name, $value);
        }

        // init form
        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($data);

        // checks
        if ($data['pueue_path']) {
            $pueuePath = $cli->validateCommand($data['pueue_path']);
            if (false === $pueuePath) {
                $messenger->addError("Pueue client not found ({$data['pueue_path']}).");
            }
        } else {
            $pueuePath = $cli->getCommandPath('pueue');
            if (false === $pueuePath) {
                $messenger->addError("Pueue client not found.");
            }
        }

        if ($pueuePath) {
            $version = $cli->execute(
                sprintf('%s --version', escapeshellcmd($pueuePath) )
            );
            if ( strpos($version, 'Pueue client') !== 0 ) {
                $messenger->addError('Invalid pueue client. ');
            } else {
                $version = str_replace('Pueue client ', '', $version);
                $messenger->addSuccess("Pueue client {$version} found ({$pueuePath}).");
                $groups = $cli->execute(
                    sprintf('%s group', escapeshellcmd($pueuePath) )
                );
                if (false === $groups) {
                    $messenger->addError('Failed to connect to pueue service.');
                } else {
                    $messenger->addSuccess('Pueue service running.');
                }
            }
        }

        $form->prepare();
        return $view->formCollection($form);
    }

    /**
     * Handle Configuration Form
     *
     * @param AbstractController $controller
     * @return bool
     */
    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        // init & validate form
        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        // sanitize data
        $params['pueue_path'] = trim($params['pueue_path']) == '' ? null : trim($params['pueue_path']);
        $params['pueue_group'] = trim($params['pueue_group']) == '' ? null : trim($params['pueue_group']);

        // save settings
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($params as $name => $value) {
            if (array_key_exists($name, $defaultSettings)) {
                $settings->set($name, $value);
            }
        }

        return true;
    }
}
