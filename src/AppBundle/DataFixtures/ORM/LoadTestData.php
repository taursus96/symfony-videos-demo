<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieOrder;
use AppBundle\Entity\User;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadTestData implements FixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@test.test');
        $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user, 'test'));
        $manager->persist($user);

        $user2 = new User();
        $user2->setUsername('test2');
        $user2->setEmail('test2@test.test');
        $user2->setPassword($this->container->get('security.password_encoder')->encodePassword($user2, 'test2'));
        $manager->persist($user2);

        $user3 = new User();
        $user3->setUsername('test-with-no-access-to-private-and-paid-movies');
        $user3->setEmail('test-with-no-access-to-private-and-paid-movies@test.test');
        $user3->setPassword($this->container->get('security.password_encoder')->encodePassword($user3, 'test'));
        $manager->persist($user3);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@test.test');
        $admin->setPassword('admin');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin);

        $movieFree = new Movie();
        $movieFree->setTitle('free');
        $movieFree->setDescription('description');
        $movieFree->setFile('../../tests_resources/test.mp4');
        $movieFree->setPreview('../../tests_resources/preview.png');
        $movieFree->setAccess(Movie::ACCESS_FREE);
        $movieFree->setPublisher($user);
        $manager->persist($movieFree);

        $moviePrivate = new Movie();
        $moviePrivate->setTitle('private');
        $moviePrivate->setDescription('description');
        $moviePrivate->setFile('../../tests_resources/test.mp4');
        $moviePrivate->setPreview('../../tests_resources/preview.png');
        $moviePrivate->setAccess(Movie::ACCESS_PRIVATE);
        $moviePrivate->setPublisher($user);
        $manager->persist($moviePrivate);

        $moviePaid = new Movie();
        $moviePaid->setTitle('paid');
        $moviePaid->setDescription('description');
        $moviePaid->setFile('../../tests_resources/test.mp4');
        $moviePaid->setPreview('../../tests_resources/preview.png');
        $moviePaid->setAccess(Movie::ACCESS_PAID);
        $moviePaid->setPrice(100);
        $moviePaid->setPublisher($user);
        $manager->persist($moviePaid);

        $movieOrder = new MovieOrder();
        $movieOrder->setPrice(100);
        $movieOrder->setUser($user2);
        $movieOrder->setMovie($moviePaid);
        $manager->persist($movieOrder);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
