<?php

namespace Admin;

use Doctrine\ORM\EntityManager;
use function foo\func;
use Zend\Http\Header\Server;
use Zend\Mvc\MvcEvent;

use Zend\EventManager\EventInterface;
use Zend\I18n\Translator\Translator;
use Zend\Http;
use Zend\Mvc\I18n\Translator as T;
use Zend\Session\Container;
use Zend\Validator\AbstractValidator;

class Module
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\CategoryController::class => function ($container) {
                    return new Controller\CategoryController(
                        $container->get(EntityManager::class),
                        $container->get('formService'),
                        $container->get('translator')
                    );
                },
                Controller\ArticleController::class => function ($container) {
                    return new Controller\ArticleController(
                        $container->get(EntityManager::class),
                        $container->get('formService'),
                        $container->get('translator')
                    );
                },
                Controller\UserController::class => function ($container) {
                    return new Controller\UserController(
                        $container->get(EntityManager::class),
                        $container->get('formService'),
                        $container->get('translator')
                    );
                },
                Controller\AdminController::class => function ($container) {
                    return new Controller\AdminController(
                        $container->get(EntityManager::class),
                        $container->get('formService'),
                        $container->get('translator')
                    );
                },
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'admin_breadcrumbs' => Service\BreadcrumbService::class,
            ],
        ];
    }

    public function getControllerPluginConfig()
    {
        return [
            'factories' => [
                'checkAdmin' => function ($container) {
                    return new Controller\Plugin\CheckAdmin(
                        $container->get(EntityManager::class)
                    );
                },
            ],
            'invokables' => [
                'decryptAdmin'     => Controller\Plugin\DecryptAdmin::class,
                'editTheFieldRole' => Controller\Plugin\EditTheFieldRole::class,

            ],
        ];
    }

    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                'getParentCategoryName' => function ($container)
                {
                    return new View\Helper\GetParentCategoryName(
                        $container->get(EntityManager::class)
                    );
                }
            ],
            'invokables' => [
                'getTooltip'  => View\Helper\GetTooltip::class,
            ],
        ];
    }

    public function onBootstrap(MvcEvent $e)
    {
        $sharedManager = $e->getApplication()->getEventManager()->getSharedManager();

        $sharedManager->attach(
            __NAMESPACE__,
            MvcEvent::EVENT_DISPATCH,
            function ($e) {
                $translator = $e->getApplication()->getServiceManager()->get('translator');

                $container = new Container('language');
                $lang = $container->language;

                if (! $lang) {
                    $lang = 'en_US';
                }

                AbstractValidator::setDefaultTranslator($translator);

                $translator->setLocale($lang);
            },
            100
        );

        $sharedManager->attach(
            __NAMESPACE__,
            MvcEvent::EVENT_DISPATCH,
            function ($e) {
                $controller = $e->getTarget();
                $user = $controller->identity();
                $controller->checkAdmin($user, $controller);
            },
            1000
        );
    }
}
