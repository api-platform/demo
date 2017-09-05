<?php

namespace AppBundle\Action;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Book;

class TestAction {
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * TestAction constructor.
     * @param EntityManager $entityManager
     */
    public function __construct (EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

     /**
     * @Route(
     *     name="book_special",
     *     path="/books/{id}/special",
     *     defaults={"_api_resource_class"=Book::class, "_api_item_operation_name"="special"}
     * )
     * @Method("GET")
     */
    public function __invoke($data) // API Platform retrieves the PHP entity using the data provider then (for POST and
                                    // PUT method) deserializes user data in it. Then passes it to the action. Here $data
                                    // is an instance of Book having the given ID. By convention, the action's parameter
                                    // must be called $data.
    {
        /**
         * @var $data Book
         */

        $data->setAuthor(get_class($this->entityManager));
        return $data; // API Platform will automatically validate, persist (if you use Doctrine) and serialize an entity
                      // for you. If you prefer to do it yourself, return an instance of Symfony\Component\HttpFoundation\Response
    }
}
