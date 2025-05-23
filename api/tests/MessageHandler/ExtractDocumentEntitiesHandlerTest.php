<?php
// tests/MessageHandler/ExtractDocumentEntitiesHandlerTest.php
namespace App\Tests\MessageHandler;

use App\Entity\Document;
use App\Message\ExtractDocumentEntities;
use App\MessageHandler\ExtractDocumentEntitiesHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ExtractDocumentEntitiesHandlerTest extends TestCase
{
    public function testHandleCreatesEntities()
    {
        // 1) Prepare a dummy Document with some raw content
        $doc = new Document();
        $doc->setRawContent('Alice went to Paris.');

        // 2) Mock the EntityRepository so find() returns our dummy Document
        $repoMock = $this->createMock(EntityRepository::class);
        $repoMock
            ->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($doc);

        // 3) Mock the EntityManager to return that repo mock
        $persisted = [];
        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->method('getRepository')
            ->with(Document::class)
            ->willReturn($repoMock);
        $em->expects($this->atLeastOnce())
            ->method('persist')
            ->willReturnCallback(function($entity) use (&$persisted) {
                $persisted[] = $entity;
            });
        $em
            ->expects($this->once())
            ->method('flush');

        // 4) Mock HTTP client to return fake NER results
        $fakeResponse = $this->createMock(ResponseInterface::class);
        $fakeResponse
            ->method('toArray')
            ->willReturn([
                ['word' => 'Alice', 'entity_group' => 'PER'],
                ['word' => 'Paris', 'entity_group' => 'LOC'],
            ]);
        $http = $this->createMock(HttpClientInterface::class);
        $http
            ->method('request')
            ->willReturn($fakeResponse);

        // 5) Instantiate handler (no RE model for simplicity)
        $handler = new ExtractDocumentEntitiesHandler(
            $em,
            $http,
            'fake-token',
            'dbmdz/bert-large-cased-finetuned-conll03-english',
            null
        );

        // 6) Invoke handler
        $handler(new ExtractDocumentEntities(123));

        // 7) Assert exactly two ExtractedEntity objects were persisted
        $extracted = array_filter(
            $persisted,
            fn($e) => $e instanceof \App\Entity\ExtractedEntity
        );
        $this->assertCount(2, $extracted);
    }
}
