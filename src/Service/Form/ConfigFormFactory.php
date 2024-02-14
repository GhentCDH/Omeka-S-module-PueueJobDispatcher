<?php
namespace PueueJobDispatcher\Service\Form;

use PueueJobDispatcher\Form\ConfigForm;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $form = new ConfigForm(null, $options ?? []);
        return $form;
    }
}