<?php

namespace App\Controller;

use App\Entity\Post;
use App\Service\PostService;
use App\Traits\ValidationHandlerTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\PostRequestType;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{
    use ValidationHandlerTrait;

    public function __construct(
        private PostService $postService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/api/posts', name: 'create_post', methods: ['POST'])]
    public function createPost(Request $request, FormFactoryInterface $formFactory): JsonResponse
    {
        $form = $formFactory->create(PostRequestType::class);
        $form->submit(json_decode($request->getContent(), true));

        if ($errorResponse = $this->handleValidationErrors($form)) {
            return $errorResponse;
        }

        $post = $form->getData();
        $this->postService->savePost($post);

        $jsonData = $this->serializer->serialize($post, 'json', ['groups' => 'post:read']);

        return new JsonResponse($jsonData, Response::HTTP_CREATED);
    }

    #[Route('/api/posts', name: 'get_all_posts', methods: ['GET'])]
    public function getAllPosts(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $paginationData = $this->postService->getAllPostsPaginated($page, $limit);

        $jsonData = $this->serializer->serialize($paginationData, 'json', ['groups' => 'post:read']);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/api/posts/{id}', name: 'get_post', methods: ['GET'])]
    public function getPost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return new JsonResponse(['status' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonData = $this->serializer->serialize($post, 'json', ['groups' => 'post:read']);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/api/posts/{id}', name: 'update_post', methods: ['PUT'])]
    public function updatePost(int $id, Request $request, FormFactoryInterface $formFactory): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return new JsonResponse(['status' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $formFactory->create(PostRequestType::class, $post);
        $form->submit(json_decode($request->getContent(), true));

        if ($errorResponse = $this->handleValidationErrors($form)) {
            return $errorResponse;
        }

        $this->postService->updatePost($post);

        $jsonData = $this->serializer->serialize($post, 'json', ['groups' => 'post:read']);

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    #[Route('/api/posts/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function deletePost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return new JsonResponse(['status' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $this->postService->deletePost($post);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
