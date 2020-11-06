<?php

namespace HitcKit\AuthBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use HitcKit\AuthBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
     protected $passwordEncoder;

     public function __construct(UserPasswordEncoderInterface $passwordEncoder)
     {
         $this->passwordEncoder = $passwordEncoder;
     }

    public function load(ObjectManager $manager)
    {
        $user = (new User())
            ->setEmail('maksim@24webdev.ru')
            ->setRoles(['ROLE_SUPER_ADMIN'])
        ;

        $password = $this->passwordEncoder->encodePassword($user, 'hex0016');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();
    }
}
