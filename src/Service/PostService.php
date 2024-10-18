<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostRepository $postRepository,
    ) {
    }

    public function savePost(Post $post): void
    {
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    public function updatePost(Post $post): void
    {
        $this->entityManager->flush();
    }

    public function deletePost(Post $post): void
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function getAllPostsPaginated(int $page, int $limit): array
    {
        $pagerfanta = $this->postRepository->getPaginatedPosts($page, $limit);
        $posts = $pagerfanta->getCurrentPageResults();

        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'created_at' => $post->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updated_at' => $post->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        $pagination = [
            'total_items' => $pagerfanta->getNbResults(),
            'current_page' => $pagerfanta->getCurrentPage(),
            'last_page' => $pagerfanta->getNbPages(),
            'items_per_page' => $pagerfanta->getMaxPerPage(),
        ];

        return [
            'data' => $data,
            'pagination' => $pagination,
        ];
    }

    public function getPostById(int $id): ?Post
    {
        return $this->entityManager->getRepository(Post::class)->find($id);
    }
}
