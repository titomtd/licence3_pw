<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Form\PostFormType;
use App\Repository\PostLikeRepository;
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
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/post/{id}/like", name="post_like")
     */
    public function like(Post $post, ObjectManager $manager, PostLikeRepository $likerepo) : Response {
        $user = $this->getUser();

        if(!$user) return $this->json(['code' => 403, 'message' => 'Pas autorisé'], 403);

        if($post->isLikedByUser($user)){
            $like = $likerepo->findOneBy([
                'post' => $post,
                'user' => $user
            ]);
            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'Like bien supprimé',
                'likes' => $likerepo->count(['post' => $post])], 200);
        }

        $like = new PostLike();
        $like->setPost($post)
            ->setUser($user);

        $manager->persist($like);
        $manager->flush();

        return $this->json(['code' => 200, 'message' => 'Like ajouté', 'likes' => $likerepo->count(['post' => $post])], 200);
    }
}
