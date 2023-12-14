<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Book;
use App\Entity\Bookmark;
use App\Entity\Review;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\ValidatorException;

final class UniqueUserBookValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security,
        private readonly ManagerRegistry $registry,
        private readonly PropertyAccessorInterface $propertyAccessor
    ) {}

    /**
     * @param Bookmark|Review|null $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUserBook) {
            throw new UnexpectedTypeException($constraint, UniqueUserBook::class);
        }

        $user = $this->security->getUser();
        if (!$value || !$user || !($book = $this->propertyAccessor->getValue($value, 'book'))) {
            return;
        }

        if (!$book instanceof Book) {
            throw new UnexpectedValueException($value, Book::class);
        }

        $className = ClassUtils::getRealClass($value::class);
        $manager = $this->registry->getManagerForClass($className);
        if (!$manager) {
            throw new ValidatorException(sprintf('"%s" is not a valid entity.', $className));
        }

        if ($manager->getRepository($className)->findOneBy(['user' => $user, 'book' => $book])) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
