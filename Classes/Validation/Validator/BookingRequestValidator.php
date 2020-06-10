<?php

/**
 * BookingRequestValidator.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Validation\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * BookingRequestValidator.
 */
class BookingRequestValidator extends AbstractValidator
{
    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param mixed $value
     */
    protected function isValid($value)
    {
        /** @var ConjunctionValidator $validator */
        $validator = GeneralUtility::makeInstance(ValidatorResolver::class)->getBaseValidatorConjunction(\get_class($value));
        /** @var \TYPO3\CMS\Extbase\Error\Result $result */
        $result = $validator->validate($value);
        foreach ($result->getFlattenedErrors() as $property => $errors) {
            foreach ($errors as $error) {
                $this->result->forProperty($property)
                    ->addError($error);
            }
        }
    }
}
