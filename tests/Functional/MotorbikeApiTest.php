<?php

namespace App\Tests\Functional;

use App\Entity\Motorbike;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MotorbikeApiTest extends WebTestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]));
        $response = json_decode($this->client->getResponse()->getContent());

        $this->token = $response->token;

        $container = $this->client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // Crear datos en la base de datos
        $date = new \DateTimeImmutable();
        $motorbike = new Motorbike();
        $motorbike->setModel('Shadow');
        $motorbike->setEngineCapacity(125);
        $motorbike->setBrand('Honda');
        $motorbike->setType('Custom');
        $motorbike->setExtras(['croma']);
        $motorbike->setWeight(145);
        $motorbike->setLimitedEdition(false);
        $motorbike->setCreatedAt($date);
        $motorbike->setUpdatedAt($date);
        $entityManager->persist($motorbike);
        $entityManager->flush();
    }

    public function testCreateMotorbikeSuccess(): void
    {
        $this->client->request('POST', '/api/motorbikes', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'model' => 'string',
            'engineCapacity' => 0,
            'brand' => 'string',
            'type' => 'string',
            'extras' => [
              'string'
            ],
            'weight' => 0,
            'limitedEdition' => true
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(201);
    }

    public function testCreateMotorbikeEdgeCase(): void
    {
        $this->client->request('POST', '/api/motorbikes', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'brand' => ''
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(422);
    }

    public function testGetMotorbikeSuccess(): void
    {
        // Assuming a customer with ID 1 exists in the test database.
        $this->client->request('GET', '/api/motorbikes/1', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetMotorbikeEdgeCase(): void
    {
        $this->client->request('GET', '/api/motorbikes/999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateMotorbikeSuccess(): void
    {
        $this->client->request('PATCH', '/api/motorbikes/1', [], [], [
            'CONTENT_TYPE' => 'application/vnd.api+json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'model' => 'string',
            'engineCapacity' => 0,
            'brand' => 'string',
            'type' => 'string',
            'extras' => [
              'string'
            ],
            'weight' => 0,
            'limitedEdition' => true
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
    }

    public function testUpdateMotorbikeEdgeCase(): void
    {
        $this->client->request('PATCH', '/api/motorbikes/999', [], [], [
            'CONTENT_TYPE' => 'application/vnd.api+json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'brand' => 'Non-existent'
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteMotorbikeSuccess(): void
    {
        $this->client->request('DELETE', '/api/motorbikes/1', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteMotorbikeEdgeCase(): void
    {
        $this->client->request('DELETE', '/api/motorbikes/999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(404);
    }

    protected function tearDown(): void
    {
        $container = $this->client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');
        $connection->executeStatement('TRUNCATE TABLE motorbike');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1;');

        parent::tearDown();
    }
}