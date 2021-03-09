<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPwdType;
use App\Form\CheckEmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * Encodeur de mot de passe
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers la page de listing
        if ($this->getUser()) {
            return $this->redirectToRoute('tasks_listing');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }

    /**
     *
     * @Route("/checkEmail", name="app_checkEmail")
     */
    public function checkEmail(Request $request)
    {

        $form = $this->createform(CheckEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $userEmail = $form['email']->getData();
            $existEmail =
                $this->getDoctrine()->getRepository(User::class)->findOneBy(array('email' => $userEmail));
            //dd($existEmail);
            if ($existEmail) {

                return $this->redirectToRoute(
                    'app_forgotPwd',
                    ['id' => $existEmail->getId()]
                );
            }
        }
        return $this->render('security/checkEmail.html.twig', [
            'form'
            => $form->createView(),
            'title' => 'Veuillez entrer votre adresse mail'
        ]);
    }

    /**
     * @Route("/forgotPwd/{id}", name="app_forgotPwd",requirements={"id"="\d+"})
     *
     */
    public function forgotPwd(User $id, Request $request)
    {
        $user =
            $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $id]);
        // dd($user);
        $form = $this->createform(ForgotPwdType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $hash = $this->encoder->encodePassword(
                $user,
                $form['password']->getdata()
            );
            //dd($hash);
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('app_login');
        }
        return $this->render(
            'security/checkEmail.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Veuillez changer votre mot de passe'
            ]
        );
    }
}
