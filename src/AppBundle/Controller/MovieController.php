<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieComment;

use AppBundle\Form\MovieType;
use AppBundle\Form\MovieCommentType;

use AppBundle\Service\MovieAccessService;
use AppBundle\Service\MovieVotingService;
use AppBundle\Service\MovieStreamingService;
use AppBundle\Service\MovieViewsCounterService;

use AppBundle\Interfaces\IVote;

use AppBundle\Util\FormErrors;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MovieController extends Controller
{
    /**
     * @Route("/movie/upload", name="movie_upload")
     * @Security("is_fully_authenticated()")
     */
    public function uploadAction(Request $request): Response
    {
        /** @var Movie $movie */
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $movie->setPublisher($this->getUser());

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($movie);
                $em->flush();

                return new JsonResponse([
                    'result' => 'URL',
                    'url' => $this->generateUrl('movie', ['id' => $movie->getId()])
                ]);
            } else {
                return new JsonResponse([
                    'result' => 'ERRORS',
                    'errors' => FormErrors::getInstance()->getErrorMessages($form)
                ]);
            }
        } else {
            return $this->render('movie/upload.html.twig', [
                'form' => $form->createView(),
                'MOVIE_ACCESS_TYPE_PAID' => Movie::ACCESS_PAID
            ]);
        }
    }

    /**
     * @Route("/movie/stream/preview/{id}", name="movie_stream_preview", requirements={"id": "\d+"})
     */
    public function movieStreamPreviewAction(Request $request, Movie $movie)
    {
        $filePath = $this->container->getParameter('movie_previews_directory') . $movie->getPreview();
        return new BinaryFileResponse($filePath);
    }

    /**
     * @Route("/movie/stream/{id}", name="movie_stream", requirements={"id": "\d+"})
     */
    public function movieStreamAction(Request $request, Movie $movie)
    {
        /** @var MovieAccessService $movieAccess */
        $movieAccess = $this->get('app.movie_access');
        /** @var MovieStreamingService $movieStreaming */
        $movieStreaming = $this->get('app.movie_streaming');

        if (!$movieAccess->hasAccess($this->getUser(), $movie)) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $moviePath = $this->getParameter('movies_directory') . '/' . $movie->getFile();
        $response = $movieStreaming->getResponse($request, $moviePath);
        $response->sendHeaders();
        $response->sendContent();
    }

    /**
     * @Route("/movie/{id}", name="movie", requirements={"id": "\d+"})
     */
    public function movieAction(Request $request, Movie $movie): Response
    {
        /** @var MovieAccessService $movieAccess */
        $movieAccess = $this->get('app.movie_access');
        if (!$movieAccess->hasAccess($this->getUser(), $movie)) {
            if ($movie->getAccess() === Movie::ACCESS_PAID) {
                return $this->redirect($this->generateUrl('movie_preview', ['id' => $movie->getId()]));
            } else {
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        /** @var MovieComment $comment */
        $comment = new MovieComment();
        $commentForm = $this->createForm(MovieCommentType::class, $comment, ['action' => $this->generateUrl('movie_comment', ['id' => $movie->getId()])]);

        /** @var MovieViewsCounterService $movieViewsCounter */
        $movieViewsCounter = $this->get('app.movie_views_counter');
        $movieViewsCounter->countView($this->getUser(), $movie);

        $this->getDoctrine()->getManager()->flush();

        return $this->render('movie/view.html.twig', [
            'movie' => $movie,
            'comments' => $movie->getComments(),
            'commentForm' => $commentForm->createView(),
            'showPreview' => false,
            'showSettings' => $movieAccess->canModify($this->getUser(), $movie),
            'VOTE_TYPE_THUMBS_UP' => IVote::THUMBS_UP,
            'VOTE_TYPE_THUMBS_DOWN' => IVote::THUMBS_DOWN
        ]);
    }

    /**
     * @Route("/movie/preview/{id}", name="movie_preview", requirements={"id": "\d+"})
     */
    public function moviePreviewAction(Request $request, Movie $movie): Response
    {
        /** @var MovieAccessService $movieAccess */
        $movieAccess = $this->get('app.movie_access');
        if ($movieAccess->hasAccess($this->getUser(), $movie)) {
            return $this->redirect($this->generateUrl('movie', ['id' => $movie->getId()]));
        } elseif ($movie->getAccess() !== Movie::ACCESS_PAID) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('movie/view.html.twig', [
            'movie' => $movie,
            'comments' => $movie->getComments(),
            'showPreview' => true,
            'showSettings' => false,
            'VOTE_TYPE_THUMBS_UP' => IVote::THUMBS_UP,
            'VOTE_TYPE_THUMBS_DOWN' => IVote::THUMBS_DOWN
        ]);
    }

    /**
     * @Route("/movie/vote/{id}/{voteType}", name="movie_vote")
     * @Security("is_fully_authenticated()")
     */
    public function movieVoteAction(Request $request, Movie $movie, int $voteType): JsonResponse
    {
        /** @var MovieAccessService $movieAccess */
        $movieAccess = $this->get('app.movie_access');
        if (!$movieAccess->hasAccess($this->getUser(), $movie)) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        /** @var VotingService $votingService */
        $votingService = $this->get('app.voting');
        $votingService->vote($this->getUser(), $movie, $voteType);

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'thumbsUp' => $movie->getThumbsUp(),
            'thumbsDown' => $movie->getThumbsDown()
        ]);
    }

    /**
     * @Route("/movie/settings/{id}", name="movie_settings", requirements={"id": "\d+"})
     * @Security("is_fully_authenticated()")
     */
    public function settingsAction(Request $request, Movie $movie): Response
    {
        /** @var MovieAccessService $movieAccessService */
        $movieAccessService = $this->get('app.movie_access');
        if (!$movieAccessService->canModify($this->getUser(), $movie)) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $form = $this->createForm(MovieType::class, $movie, ['includeFile' => false, 'includePreview' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirect($this->generateUrl('movie', ['id' => $movie->getId()]));
        }

        return $this->render('movie/settings.html.twig', [
            'form' => $form->createView(),
            'MOVIE_ACCESS_TYPE_PAID' => Movie::ACCESS_PAID
        ]);
    }
}
