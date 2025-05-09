<?php

namespace App\EventSubscriber;

use App\Entity\Document;
use App\Message\FetchDocumentContent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;

class DocumentCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        // After the write operation (i.e. after the DB insert)
        return [
            KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE],
        ];
    }

    public function onPostWrite(ViewEvent $event): void
    {
        $document = $event->getControllerResult();
        $method   = $event->getRequest()->getMethod();

        // Only dispatch on POST of a Document
        if (!$document instanceof Document || Request::METHOD_POST !== $method) {
            return;
        }

        // Dispatch our message with the new Document’s ID
        $this->bus->dispatch(new FetchDocumentContent($document->getId()));
    }
}
