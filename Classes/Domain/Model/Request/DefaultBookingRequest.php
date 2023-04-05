<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Request;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;

/**
 * DefaultBookingRequest.
 */
class DefaultBookingRequest extends AbstractBookingRequest
{
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $firstName;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $lastName;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    #[Extbase\Validate(['validator' => EmailAddressValidator::class])]
    protected string $email;

    protected string $phone;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $street;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $houseNumber;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $zip;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $city;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $country;

    protected string $message;

    /**
     * Get first name.
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set first name.
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Get last name.
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Set last name.
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Get E-Mail.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set E-Mail.
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Get phone.
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Set phone.
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Get street.
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Set street.
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * Get house number.
     */
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * Set house number.
     */
    public function setHouseNumber(string $houseNumber): void
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * Get ZIP.
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Set ZIP.
     */
    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    /**
     * Get city.
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Set city.
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * Get country.
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Set country.
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * Get message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set message.
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
