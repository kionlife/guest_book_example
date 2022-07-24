<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/posts", name="app_posts")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findBy([
            'published' => 1,
            'archived'  => 0
        ]);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/{id}", name="app_post")
     */
    public function show(ManagerRegistry $doctrine, int $id): Response
    {

        $post = $doctrine->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id '.$id
            );
        }


        return $this->render('post/post.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/posts/add", name="app_posts_add")
     */
    public function addPage(ManagerRegistry $doctrine): Response
    {
        return $this->render('post/add.html.twig');
    }

    /**
     * @Route("/posts/create", name="app_posts_create")
     */
    public function add(ManagerRegistry $doctrine, ValidatorInterface $validator, Request $request): Response
    {

//        dd($request);
        $entityManager = $doctrine->getManager();
        $post = new Post();
        $post->setAuthor($request->request->get('author'));
        $post->setEmail($request->request->get('email'));
        $post->setMessage($request->request->get('message'));
        $post->setHomepage($request->request->get('homepage'));
        $post->setCreatedAt(new \DateTimeImmutable('now'));
        $post->setPublished(0);

        $entityManager->persist($post);
        $entityManager->flush();

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        return new Response('Post saved');
    }
}
