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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @Route("/posts/{language}", name="homeFilter")
     */
    public function index($language = null, PostRepository $postRepository, UserRepository $userRepository): Response
    {
        if($language){
            $posts = $postRepository->findByLanguage($language); 
            $search = true;
        }
        else{
            $posts = $postRepository->findPosts();
            $search = false;
        }

        $users = $userRepository->findAll();

        $nbPhp = $postRepository->countBy('php');
        $nbJava = $postRepository->countBy('java');
        $nbPython = $postRepository->countBy('python');
        $nbTotal = count($postRepository->findAll());

        return $this->render('post/index.html.twig', [
            'filterSearch' => $search,
            'nbPosts' => count($posts),
            'posts' => $posts,
            'users' => $users,
            'nb_php' => $nbPhp,
            'nb_java' => $nbJava,
            'nb_python' => $nbPython,
            'nb_total' => $nbTotal,
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

            $codeFile = $form->get('filename')->getData();
            if($codeFile){
                $filename = md5(uniqid()).'.'.$codeFile->guessExtension();
                $codeFile->move($this->getParameter('upload_file_directory'), $filename);
                $post->setFilename($filename);
            }

            $manager->persist($post);
            $manager->flush();

            $this->addFlash('success', 'app.ui.update_post_success');

            return $this->redirectToRoute('post_show', ['id' =>$post->getId()]);
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
            'editMode' => $post->getId() !== null
        ]);
    }

    /**
     * @Route("/post", name="post_search")
     */
    public function postSearch(PostRepository $postRepository, Request $request): Response
    {
        $posts = $postRepository->findAll();

        $form = $this->createFormBuilder()
            ->add('title', TextType::class,
                array('required' => false))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $posts = $postRepository->findPostByTitle($data['title']);
        }

        return $this->render('post/post_search.html.twig', [
            'posts' => $posts,
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
