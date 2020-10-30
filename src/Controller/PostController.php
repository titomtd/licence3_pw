<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
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
    public function index(PostRepository $postRepository, UserRepository $userRepository): Response
    {
        $posts = $postRepository->findAll();
        $users = $userRepository->findAll();

        $nbPhp = $postRepository->countBy('php');
        $nbJava = $postRepository->countBy('java');
        $nbPython = $postRepository->countBy('python');

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'users' => $users,
            'nb_php' => $nbPhp,
            'nb_java' => $nbJava,
            'nb_python' => $nbPython,
        ]);
    }

    /**
     * @Route("/post/show/{id}", name="post_show")
     */
    public function changeLocale(Post $post, UserRepository $userRepository)
    {
        $users = $userRepository->findAll();

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'users' => $users,
        ]);
    }

    /**
     * @Route("/post/create", name="post_create")
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
