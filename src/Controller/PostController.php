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

class PostController extends AbstractController
{
    use ValidationHandlerTrait;

    public function __construct(
        private PostService $postService
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

        $responseData = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route('/api/posts', name: 'get_all_posts', methods: ['GET'])]
    public function getAllPosts(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $paginationData = $this->postService->getAllPostsPaginated($page, $limit);

        return new JsonResponse($paginationData, Response::HTTP_OK);
    }

    #[Route('/api/posts/{id}', name: 'get_post', methods: ['GET'])]
    public function getPost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return new JsonResponse(['status' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
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

        $responseData = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/api/posts/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function deletePost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return new JsonResponse(['status' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $this->postService->deletePost($post);

        return new JsonResponse(['status' => 'Post deleted!'], Response::HTTP_OK);
    }
}
