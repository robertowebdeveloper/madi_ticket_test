<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class OnboardController extends AbstractController
{
    private $encoder;

    public function __construct( UserPasswordEncoderInterface $encoder )
    {
        $this->encoder = $encoder;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction( AuthenticationUtils $authenticationUtils ): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $User = new User();
        $form = $this->createFormBuilder( $User )
            ->add('email' , EmailType::class)
            ->add('password' , PasswordType::class)
            ->add('submit' , SubmitType::class , ['label' => 'Accedi'])
            ->getForm()
        ;

        if(! is_null( $this->getUser() ) ) {
            return $this->redirectToRoute('dashboard');
        }

        #$form->handleRequest( $request );

        if( $form->isSubmitted() ){
            #dump($form);die;
        }

        return $this->render('login.html.twig', [
            'last_username'     => $lastUsername,
            'error'             => $error,
            'form'              => $form->createView()
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction( Request $request )
    {
        $User = new User();
        $form = $this->createFormBuilder( $User )
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('mobile', TextType::class)
            ->add('password', PasswordType::class)
            ->add('role', ChoiceType::class, [
                'label' => 'Ti stai registrando come:',
                'choices'  => [
                    'Amministratore' => 'ROLE_ADMIN',
                    'Utente' => 'ROLE_USER',
                ],
                'multiple' => false,
                'expanded' => false
            ])
            ->add('submit' , SubmitType::class , ['label' => 'Registrati'])
            ->getForm()
        ;

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();

            $encodePW = $this->encoder->encodePassword( $User , $request->request->get('form')['password'] );
            $User->setPassword( $encodePW );

            $em->persist( $User );
            $em->flush();

            return $this->redirectToRoute('index', [ 'registered' => 1 ]);
        }

        return $this->render('register.html.twig' , [
            'form'      => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('index');
    }
}