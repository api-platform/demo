<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

class AuthController extends AbstractController
{
    public function loginCheck(Request $request)
    {
        $userProvider = new InMemoryUserProvider([
            'admin' => [
                'password' => 'foo',
                'roles' => array('ROLE_ADMIN'),
            ],
        ]);

        // encoder array of password encoders (see below)
        $encoderFactory = new EncoderFactory([
            User::class => new PlaintextPasswordEncoder(),
        ]);

        $daoProvider = new DaoAuthenticationProvider(
            $userProvider,
            new UserChecker(),
            'secured_area',
            $encoderFactory
        );

        // set the user token with fake credentials
        $unauthenticatedToken = new UsernamePasswordToken(
            $request->request->get('username'),
            $request->request->get('password'),
            'secured_area'
        );

        return $this->json([
            'token' => $daoProvider->authenticate($unauthenticatedToken),
        ]);
    }
}
