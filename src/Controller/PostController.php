<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/create", name="psot_create")
     */
    public function create(Request $request, ObjectManager $manager, ?UserInterface $user): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $post->setUser($user);
            $post->setCreatedAt(new \DateTime());
            $post->setScore(0);
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
