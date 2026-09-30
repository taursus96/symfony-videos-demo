<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\MovieOrder;
use AppBundle\Service\DotpayMovieOrderResolverService;
use Symfony\Component\HttpFoundation\Request;

class DotpayMovieOrderResolverServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testAcceptsAnAmountMatchingTheOrderPrice()
    {
        $this->assertTrue($this->checkOperationAmount(1234, '12.34'));
    }

    public function testRejectsAnAmountThatDoesNotMatchTheOrderPrice()
    {
        $this->assertFalse($this->checkOperationAmount(1234, '12.35'));
    }

    public function testRejectsMalformedAmounts()
    {
        $this->assertFalse($this->checkOperationAmount(1234, '12.340'));
        $this->assertFalse($this->checkOperationAmount(1234, '-12.34'));
        $this->assertFalse($this->checkOperationAmount(1234, '1e1'));
    }

    private function checkOperationAmount($price, $amount)
    {
        $order = new MovieOrder();
        $order->setPrice($price);

        $request = Request::create('/', 'POST', ['operation_amount' => $amount]);
        $resolver = (new \ReflectionClass(DotpayMovieOrderResolverService::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(DotpayMovieOrderResolverService::class, 'checkOperationAmount');
        $method->setAccessible(true);

        return $method->invoke($resolver, $order, $request);
    }
}
