<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AuthController extends AbstractController
{

    public function login_check(Request $request)
    {
        // set the in_memory provider
        $userProvider = new InMemoryUserProvider(
            array(
                'admin' => array(
                    // password is "foo"
                    'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
                    'roles'    => array('ROLE_ADMIN'),
                ),
            )
        );

        // encoder array of password encoders (see below)
        $encoderFactory = new EncoderFactory([
            User::class => new MessageDigestPasswordEncoder('sha512', true, 5000)
        ]);

        // define the data object provider
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
            'token' => $daoProvider->authenticate($unauthenticatedToken)
        ]);
    }

}
