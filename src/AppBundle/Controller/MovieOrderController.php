<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movie;

use AppBundle\Form\MovieOrderWithDotpayType;

use AppBundle\Service\MovieOrderingService;
use AppBundle\Service\DotpayMovieOrderResolverService;

use AppBundle\Exception\OrderingMovieThatIsNotOrderableException;
use AppBundle\Exception\OrderingMovieThatUserAlreadyHasAccessToException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

class MovieOrderController extends Controller
{

    /**
     * @Route("/movie/order/{id}", name="movie_order")
     * @Security("is_fully_authenticated()")
     */
    public function orderAction(Request $request, Movie $movie): Response
    {
        /** @var MovieOrderingService $movieOrderingService */
        $movieOrderingService = $this->get('app.movie_ordering');
        /** @var Translator $translator */
        $translator = $this->get('translator');

        try {
            $order = $movieOrderingService->order($this->getUser(), $movie);
            $this->getDoctrine()->getManager()->flush();

            $form = $this->createForm(MovieOrderWithDotpayType::class, null, [
                'action' => $this->getParameter('dotpay_form_action'),
                'control' => $order->getId(),
                'amount' => $order->getPriceAsFloat(),
                'description' => $translator->trans('order.description', ['{{ title }}' => $movie->getTitle()], 'movie_order'),
                'id' => $this->getParameter('dotpay_id'),
                'api_version' => $this->getParameter('dotpay_api_version'),
                'currency' => $this->getParameter('dotpay_currency'),
                'lang' => $this->getParameter('dotpay_lang'),
                'type' => $this->getParameter('dotpay_type'),
                'URL' => $this->generateUrl('movie_complete_order', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

            return $this->render('movie/order.html.twig', [
                'form' => $form->createView(),
                'description' => $translator->trans('order.description', ['{{ title }}' => $movie->getTitle()], 'movie_order')
            ]);
        } catch (OrderingMovieThatUserAlreadyHasAccessToException $ex) {
            return $this->redirect($this->generateUrl('homepage'));
        } catch (OrderingMovieThatIsNotOrderableException $ex) {
            return $this->redirect($this->generateUrl('homepage'));
        }
    }

    /**
     * @Route("/movie/complete_order", name="movie_complete_order")
     */
    public function completeOrderAction(Request $request): Response
    {
        /** @var Response $response */
        $response = new Response();

        try {
            /** @var DotpayMovieOrderResolverService $dotpayMovieOrderResolver */
            $dotpayMovieOrderResolver = $this->get('app.dotpay_movie_order_resolver');
            $resolved = $dotpayMovieOrderResolver->resolve($request);
            $this->getDoctrine()->getManager()->flush();

            if ($resolved) {
                $response->setContent('OK');
            } else {
                $response->setContent('NOT_RESOLVED');
            }
        } catch (\Exception $e) {
            $response->setContent('EXCEPTION');
        }

        return $response;
    }
}
