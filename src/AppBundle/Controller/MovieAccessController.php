<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieAccess;

use AppBundle\Form\MovieAccessType;

use AppBundle\Service\MovieAccessService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MovieAccessController extends Controller
{
    /**
     * @Route("/movie/give_access/{id}", name="movie_give_access")
     * @Security("is_fully_authenticated()")
     */
    public function giveAccessAction(Request $request, Movie $movie): Response
    {
        /** @var MovieAccessService $movieAccessService */
        $movieAccessService = $this->get('app.movie_access');

        if (!$movieAccessService->canModify($this->getUser(), $movie)) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        /** @var MovieAccess $movieAccess */
        $movieAccess = new MovieAccess();
        $form = $this->createForm(MovieAccessType::class, $movieAccess);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movieAccessService->giveAccess($this->getUser(), $movie, $movieAccess->getUser());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('movie', ['id' => $movie->getId()]));
        }

        return $this->render('movie/give_access.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
