<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\MovieOrder;
use AppBundle\Entity\MovieAccess;
use AppBundle\Entity\User;

use AppBundle\Service\MovieOrderingService;

use AppBundle\Exception\InvalidSignatureException;
use AppBundle\Exception\IpIsNotInAcceptedRangeException;
use AppBundle\Exception\OperationAmountNotEqualToMoviePriceException;

class DotpayMovieOrderResolverService
{
    /** @var EntityManager $em */
    protected $em;
    /** @var MovieOrderingService $movieOrderingService */
    protected $movieOrderingService;

    public function __construct(EntityManager $entityManager, MovieOrderingService $movieOrderingService, array $acceptedIpAdresses, string $pin)
    {
        $this->em = $entityManager;
        $this->movieOrderingService = $movieOrderingService;
        $this->acceptedIpAdresses = $acceptedIpAdresses;
        $this->pin = $pin;
    }

    public function resolve(Request $request): bool
    {
        $movieOrder = $this->getMovieOrder($request);

        if (!$this->checkIp($request)) {
            throw new IpIsNotInAcceptedRangeException();
        } elseif (!$this->checkSignature($request)) {
            throw new InvalidSignatureException();
        } elseif (!$this->checkOperationAmount($movieOrder, $request)) {
            throw new OperationAmountNotEqualToMoviePriceException();
        }

        return $this->movieOrderingService->completeOrder($movieOrder);
    }

    protected function checkIp(Request $request): bool
    {
        return in_array($request->getClientIp(), $this->acceptedIpAdresses);
    }

    protected function checkSignature(Request $request): bool
    {
        return $request->request->get('signature') === $this->getSignature($request);
    }

    protected function checkOperationAmount(MovieOrder $movieOrder, Request $request): bool
    {
        return $movieOrder->getPriceAsFloat() === $request->request->get('operation_amount');
    }

    protected function getSignature(Request $request): string
    {
        $sign =
            $this->pin.
            $request->request->get('id').
            $request->request->get('operation_number').
            $request->request->get('operation_type').
            $request->request->get('operation_status').
            $request->request->get('operation_amount').
            $request->request->get('operation_currency').
            $request->request->get('operation_withdrawal_amount').
            $request->request->get('operation_commission_amount').
            $request->request->get('operation_original_amount').
            $request->request->get('operation_original_currency').
            $request->request->get('operation_datetime').
            $request->request->get('operation_related_number').
            $request->request->get('control').
            $request->request->get('description').
            $request->request->get('email').
            $request->request->get('p_info').
            $request->request->get('p_email').
            $request->request->get('channel').
            $request->request->get('channel_country').
            $request->request->get('geoip_country');
        return hash('sha256', $sign);
    }

    protected function getMovieOrder(Request $request): MovieOrder
    {
        $orderId = $request->request->get('control');
        /** @var MovieOrder $movieOrder */
        $movieOrder = $this->em->getRepository('AppBundle:MovieOrder')->find($orderId);

        if (!$movieOrder) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(MovieOrder::class, [$orderId]);
        }

        return $movieOrder;
    }
}
