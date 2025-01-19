<?php

namespace App\Tests\Functional;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerApiTest extends WebTestCase
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
        $customer = new Customer();
        $customer->setName('John Doe');
        $customer->setEmail('john.doe@example.com');
        $customer->setIsSubscribed(true);
        $customer->isSubscribed();
        $entityManager->persist($customer);
        $entityManager->flush();
    }

    public function testCreateCustomerSuccess(): void
    {
        $this->client->request('POST', '/api/customers', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'isSubscribed' => true
        ]));

        $response = $this->client->getResponse();
        
        $this->assertResponseStatusCodeSame(201);
    }

    public function testCreateCustomerEdgeCase(): void
    {
        $this->client->request('POST', '/api/customers', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'name' => '',
            'isSubscribed' => true
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(422);
    }

    public function testGetCustomerSuccess(): void
    {
        // Assuming a customer with ID 1 exists in the test database.
        $this->client->request('GET', '/api/customers/1', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetCustomerEdgeCase(): void
    {
        $this->client->request('GET', '/api/customers/999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateCustomerSuccess(): void
    {
        $this->client->request('PATCH', '/api/customers/1', [], [], [
            'CONTENT_TYPE' => 'application/vnd.api+json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'name' => 'John Doe Updated',
            'email' => 'john.updated@example.com',
            'isSubscribed' => false
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
    }

    public function testUpdateCustomerEdgeCase(): void
    {
        $this->client->request('PATCH', '/api/customers/999', [], [], [
            'CONTENT_TYPE' => 'application/vnd.api+json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ], json_encode([
            'name' => 'Non-existent',
            'email' => 'non.existent@example.com',
            'isSubscribed' => true
        ]));

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteCustomerSuccess(): void
    {
        $this->client->request('DELETE', '/api/customers/1', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteCustomerEdgeCase(): void
    {
        $this->client->request('DELETE', '/api/customers/999', [], [], [
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
        $connection->executeStatement('TRUNCATE TABLE customer');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1;');

        parent::tearDown();
    }
}
