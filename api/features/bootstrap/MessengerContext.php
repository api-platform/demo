<?php

use Behat\Behat\Context\Context;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class MessengerContext implements Context
{
    /**
     * @var MessageCountAwareInterface[]
     */
    private $receivers;

    public function __construct(ContainerInterface $receivers)
    {
        $this->receivers = $receivers;
    }

    /**
     * @Then a message has been sent
     * @Then :expected messages has been sent
     */
    public function checkMessageHasBeenSentOnDoctrineConnection(int $expected = 1): void
    {
        if ($expected !== ($count = $this->receivers->get('doctrine')->getMessageCount())) {
            throw new \RuntimeException("Expected $expected message(s), got $count.");
        }
    }
}
