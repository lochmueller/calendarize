<?php

/**
 * DefaultBookingRequest.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Request;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;

/**
 * DefaultBookingRequest.
 */
class DefaultBookingRequest extends AbstractBookingRequest
{
    /**
     * First name.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $firstName;

    /**
     * Last name.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $lastName;

    /**
     * E-Mail.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    #[Extbase\Validate(['validator' => EmailAddressValidator::class])]
    protected string $email;

    /**
     * Phone.
     */
    protected string $phone;

    /**
     * Street.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $street;

    /**
     * House number.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $houseNumber;

    /**
     * ZIP.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $zip;

    /**
     * City.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $city;

    /**
     * Country.
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $country;

    /**
     * Message.
     *
     * @var string
     */
    protected string $message;

    /**
     * Get first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set first name.
     *
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Set last name.
     *
     * @param string $lastName
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Get E-Mail.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set E-Mail.
     *
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Set street.
     *
     * @param string $street
     */
    public function setStreet(string $street)
    {
        $this->street = $street;
    }

    /**
     * Get house number.
     *
     * @return string
     */
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * Set house number.
     *
     * @param string $houseNumber
     */
    public function setHouseNumber(string $houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * Get ZIP.
     *
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Set ZIP.
     *
     * @param string $zip
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Set city.
     *
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Set country.
     *
     * @param string $country
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}
