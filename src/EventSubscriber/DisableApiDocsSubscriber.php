<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Отключает доступ к документации API Platform в production
 */
class DisableApiDocsSubscriber implements EventSubscriberInterface
{
    private const array BLOCKED_ROUTES = [
        'api_doc',
        'api_entrypoint',
        'api_jsonld_context',
    ];

    public function __construct(
        private readonly string $environment
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 31],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Работаем только в production
        if ($this->environment !== 'prod') {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        // Блокируем доступ к маршрутам документации
        if (in_array($routeName, self::BLOCKED_ROUTES, true)) {
            throw new NotFoundHttpException('API documentation is disabled in production');
        }

        // Блокируем прямой доступ к /api и /api/
        $path = $request->getPathInfo();
        if ($path === '/api' || $path === '/api/') {
            throw new NotFoundHttpException('API documentation is disabled in production');
        }
    }
}
