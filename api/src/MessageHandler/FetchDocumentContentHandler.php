<?php

namespace App\MessageHandler;

use App\Entity\Document;
use App\Message\FetchDocumentContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsMessageHandler]
class FetchDocumentContentHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
    }

    public function __invoke(FetchDocumentContent $message): void
    {
        // Retrieve the Document entity
        $document = $this->entityManager
            ->getRepository(Document::class)
            ->find($message->getDocumentId());

        if (!$document) {
            // Document not found, nothing to do
            return;
        }

        try {
            $response = $this->httpClient->request('GET', $document->getUrl());
            if (200 !== $response->getStatusCode()) {
                // Non-OK response, skip
                return;
            }
            $content = $response->getContent();
        } catch (TransportExceptionInterface | ClientExceptionInterface $e) {
            // Logging could be added here if desired
            return;
        }

        // Update document with fetched content
        $document->setRawContent($content);
        $document->setFetchedAt(new \DateTimeImmutable());

        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }
}
