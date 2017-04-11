<?php

namespace Tests\AppBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Client;

use AppBundle\Entity\User;

class TestCaseBase extends WebTestCase
{
    /** @var EntityManager $em */
    protected $em;

    public function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function getUser($username)
    {
        return $this->em->getRepository('AppBundle:User')->findOneByUsername($username);
    }

    public function loginUser(string $username, Client $client): User
    {
        $user = $this->em->getRepository('AppBundle:User')->findOneByUsername($username);

        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main_firewall', $user->getRoles());

        $session = $client->getContainer()->get('session');
        $session->set('_security_' . 'main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $user;
    }
}
