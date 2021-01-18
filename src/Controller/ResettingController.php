<?php


namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Service\Mailer;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Form\ResettingType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Environment;

/**
 * @Route("/reset-password")
 */
class ResettingController extends AbstractController
{
    /**
     * @Route("/request", name="request_resetting")
     */
    public function request(UserRepository $userRepository, ObjectManager $manager, Request $request, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator, Environment $engine)
    {

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userRepository->loadUserByUsername($form->getData()['email']);

            if (!$user) {
                $this->addFlash('warning', "app.ui.email_no_exist");
                return $this->redirectToRoute("request_resetting");
            }

            $user->setToken($tokenGenerator->generateToken());
            $user->setPasswordRequestedAt(new \Datetime());
            $manager->flush();

            $message = (new \Swift_Message('[RENHELP] Reset password'))
                ->setFrom('renhelp.contact@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $engine->render('resetting/mail.html.twig', ['user' => $user]),
                    'text/html'
                )
            ;

            $mailer->send($message);
            $this->addFlash('success', "app.ui.email_send");

            return $this->redirectToRoute("security_login");
        }

        return $this->render('resetting/request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function isRequestInTime(\Datetime $passwordRequestedAt = null)
    {
        if ($passwordRequestedAt === null)
        {
            return false;
        }

        $now = new \DateTime();
        $interval = $now->getTimestamp() - $passwordRequestedAt->getTimestamp();

        $daySeconds = 60 * 10;
        $response = $interval > $daySeconds ? false : $reponse = true;
        return $response;
    }

    /**
     * @Route("/{id}/{token}", name="resetting")
     */
    public function resetting(ObjectManager $manager, User $user, $token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($user->getToken() === null || $token !== $user->getToken() || !$this->isRequestInTime($user->getPasswordRequestedAt()))
        {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(ResettingType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $user->setToken(null);
            $user->setPasswordRequestedAt(null);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "app.ui.password_has_been_reset");

            return $this->redirectToRoute('security_login');

        }

        return $this->render('resetting/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}