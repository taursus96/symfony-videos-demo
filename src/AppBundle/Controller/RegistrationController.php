<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManager;

class RegistrationController extends Controller
{
    /**
     * @Route("/registration", name="registration")
     */
    public function registrationAction(Request $request): Response
    {
        /** @var User $user */
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                $this->get('translator')->trans('registration.successful', [], 'registration')
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
