<?php

declare(strict_types=1);

namespace App\Validator;

use App\BookRepository\BookRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class BookUrlValidator extends ConstraintValidator
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
    ) {
    }

    /**
     * @param string|null $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof BookUrl) {
            throw new UnexpectedTypeException($constraint, BookUrl::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->bookRepository->find($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
