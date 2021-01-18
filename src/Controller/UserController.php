<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_index")
     */
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->findAll();

        $form = $this->createFormBuilder()
            ->add('username', TextType::class,
                array('required' => false))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $users = $userRepository->findUserByUsername($data['username']);
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/show/{id}", name="user_show")
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/my-account/dashboard", name="my_account_dashboard")
     */
    public function dashboard(?UserInterface $user)
    {
        return $this->render('myaccount/dashboard.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/my-account/update-profile", name="my_account_update_profile")
     */
    public function updateProfile(Request $request, ObjectManager $manager, ?UserInterface $user)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('pictureFileName')->getData();
            if($imageFile) {
                $filename = md5(uniqid()) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('upload_picture_directory'), $filename);
                $user->setPictureFilename($filename);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'app.ui.update_profile_success');

            return $this->redirectToRoute('my_account_update_profile');
        }
        return $this->render('myaccount/update_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/my-account/comments", name="my_account_comments")
     */
    public function allComments(?UserInterface $user)
    {
        return $this->render('myaccount/comments.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/my-account/update-password", name="my_account_update_password")
     */
    public function updatePassword(Request $request, ObjectManager $manager, ?UserInterface $user, UserPasswordEncoderInterface $encoder)
    {
       /* $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);*/

        return $this->render('myaccount/update_password.html.twig', [
            /*'user' => $user,
            'form' => $form->createView()*/
        ]);
    }
}
