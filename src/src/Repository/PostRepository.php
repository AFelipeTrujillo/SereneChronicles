<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Retrieves a query for fetching posts for the index page.
     *
     * This method creates a query to retrieve all posts from the database,
     * ordered by their creation date in descending order. This is typically
     * used for displaying posts on the index page of a blog.
     *
     * @return \Doctrine\ORM\Query The Doctrine ORM query object that can be executed to retrieve the posts.
     *
     * Example usage:
     * $query = $postRepository->findForIndexPage();
     * $posts = $query->getResult();
     */
    public function findForIndexPage(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
        ;
    }

    /**
     * Finds posts by title or content.
     *
     * This method creates a query to find posts in the database where the title or content
     * contains the specified search term. The search is performed using a SQL 'LIKE' statement.
     *
     * @param string $searchTerm The term to search for in the post titles and contents.
     *
     * @return \Doctrine\ORM\Query The Doctrine ORM query object that can be executed to retrieve the matching posts.
     *
     * Example usage:
     * $query = $postRepository->findByTitleOrContent('Symfony');
     * $posts = $query->getResult();
     */
    public function findByTitleOrContent(string $searchTerm) : \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :searchTerm')
            ->orWhere('p.content LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
        ;
    }
}
