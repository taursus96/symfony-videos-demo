<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieComment;

use AppBundle\Form\MovieCommentType;

use AppBundle\Service\MovieAccessService;
use AppBundle\Service\MovieCommentVotingService;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class MovieCommentController extends Controller
{
    /**
     * @Route("/movie/comment/{id}", name="movie_comment")
     * @Security("is_fully_authenticated()")
     */
    public function commentAction(Request $request, Movie $movie): Response
    {
        /** @var MovieAccessService $movieAccess */
        $movieAccess = $this->get('app.movie_access');

        if (!$movieAccess->hasAccess($this->getUser(), $movie)) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        /** @var MovieComment $comment */
        $comment = new MovieComment();
        $form = $this->createForm(MovieCommentType::class, $comment, ['action' => $this->generateUrl('movie_comment', ['id' => $movie->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPublisher($this->getUser());
            $comment->setMovie($movie);

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirect($this->generateUrl('movie', ['id' => $movie->getId()]));
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/movie/comment/vote/{id}/{voteType}", name="movie_comment_vote")
     * @Security("is_fully_authenticated()")
     */
    public function commentVoteAction(Request $request, MovieComment $comment, int $voteType): Response
    {
        /** @var MovieAccessService $movieAccess */
        $movieAccess = $this->get('app.movie_access');

        if (!$movieAccess->hasAccess($this->getUser(), $comment->getMovie())) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        /** @var VotingService $votingService */
        $votingService = $this->get('app.voting');
        $votingService->vote($this->getUser(), $comment, $voteType);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'commentId' => $comment->getId(),
            'thumbsUp' => $comment->getThumbsUp(),
            'thumbsDown' => $comment->getThumbsDown()
        ]);
    }
}
