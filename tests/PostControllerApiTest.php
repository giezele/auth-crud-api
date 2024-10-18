<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Doctrine\ORM\EntityManagerInterface;

class PostControllerApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $token = $this->getTestJwtToken('user1');
        $this->client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);
    }

    /**
     * @dataProvider postDataProvider
     */
    public function testCreatePost(string $title, string $content, int $expectedStatusCode, ?string $expectedError = null): void
    {
        $postData = [
        'title' => $title,
            'content' => $content,
        ];

        // Send a POST request to the create post endpoint with the pre-configured authorization
        $this->client->request(
        'POST',
        '/api/posts',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode($postData)
        );

        // Assert that the response status code is as expected
        self::assertResponseStatusCodeSame($expectedStatusCode);

        // If an error is expected, assert that the response contains the expected error message
        if ($expectedError) {
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('errors', $responseContent);
            $this->assertContains($expectedError, $responseContent['errors']);
        } else {
        // Otherwise, assert that the post was created successfully
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('id', $responseContent);
            $this->assertEquals($title, $responseContent['title']);
            $this->assertEquals($content, $responseContent['content']);
        }
    }

    public function postDataProvider(): array
    {
        return [
            //[title, content, status]
            ['', 'This is the content of the test post.', 400, 'Title should not be blank'],
            ['My Test Post', '', 400, 'Content should not be blank'],
            ['My Valid Test Post', 'This is the content of the valid test post.', 201],
        ];
    }

    public function testGetAllPosts(): void
    {
        $this->createPost();

        $this->client->request('GET', '/api/posts');

        self::assertResponseStatusCodeSame(200);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseContent);
        $this->assertArrayHasKey('pagination', $responseContent);
        $this->assertGreaterThanOrEqual(1, count($responseContent['data']));
        $this->assertArrayHasKey('total_items', $responseContent['pagination']);
    }

    public function testGetPost(): void
    {
        $postId = $this->createPost();

        $this->client->request('GET', '/api/posts/' . $postId);

        self::assertResponseStatusCodeSame(200);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('My Test Post', $responseContent['title']);
        $this->assertEquals('This is the content of the test post.', $responseContent['content']);
    }

    public function testUpdatePost(): void
    {
        $postId = $this->createPost();

        $updatedData = [
            'title' => 'Updated Test Post',
            'content' => 'This is the updated content of the test post.',
        ];

        $this->client->request(
            'PUT',
            '/api/posts/' . $postId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        self::assertResponseStatusCodeSame(200);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Test Post', $responseContent['title']);
        $this->assertEquals('This is the updated content of the test post.', $responseContent['content']);
    }

    public function testDeletePost(): void
    {
        $postId = $this->createPost();

        $this->client->request('DELETE', '/api/posts/' . $postId);

        self::assertResponseStatusCodeSame(200);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Post deleted!', $responseContent['status']);
    }

    private function createPost(
        string $title = 'My Test Post',
        string $content = 'This is the content of the test post.'
    ): int {
        $postData = [
            'title' => $title,
            'content' => $content,
        ];
        $this->client->request(
            'POST',
            '/api/posts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        return $responseContent['id'];
    }

    private function getTestJwtToken(string $username): string
    {
        $container = static::getContainer();
        $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');

        $user = new InMemoryUser(
            $username,
            'password123',
            ['ROLE_USER']
        );

        return $jwtManager->create($user);
    }
}
