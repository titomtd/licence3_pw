<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Form\PostCommentFormType;
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
    public function show(Post $post, UserRepository $userRepository, Request $request, ObjectManager $manager, ?UserInterface $user)
    {
        $comment = new PostComment();

        $form = $this->createForm(PostCommentFormType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTime());
            $comment->setPost($post);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('post_show', [
                'id' => $post->getId()
            ]);
        }

        $users = $userRepository->findAll();

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'users' => $users,
            'commentForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/post/{id}/close", name="post_close")
     */
    public function postClose(Post $post, Request $request, ObjectManager $manager){

        $post->setClose(true);
        $manager->persist($post);
        $manager->flush();
        return $this->redirectToRoute('post_show', ['id' =>$post->getId()]);
    }

    /**
     * @Route("/post/{id}/delete", name="post_delete")
     */
    public function postDelete(Post $post, Request $request, ObjectManager $manager){

        $comments = $post->getPostComments();
        foreach($comments as $comment){
            $manager->remove($comment);
            $manager->flush();
        }

        $likes = $post->getLikes();
        foreach($likes as $like){
            $manager->remove($like);
            $manager->flush();
        }

        $manager->remove($post);
        $manager->flush();

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/post/create", name="post_create")
     * @Route("/post/{id}/edit", name="post_edit")
     */
    public function create(Post $post = null, Request $request, ObjectManager $manager, ?UserInterface $user): Response
    {
        
        if(!$post){
            $post = new Post();
        }
        else if($user != $post->getUser()){
            return $this->redirectToRoute('home');
        }
        

        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if(!$post->getId()){
                $post->setUser($user);
                $post->setCreatedAt(new \DateTime());
            }
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('post_show', ['id' =>$post->getId()]);
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
            'editMode' => $post->getId() !== null
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
