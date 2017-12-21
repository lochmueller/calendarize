<?php

/**
 * DefaultBookingRequest.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Domain\Model\Request;

/**
 * DefaultBookingRequest.
 */
class DefaultBookingRequest extends AbstractBookingRequest
{
    /**
     * First name.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $firstName;

    /**
     * Last name.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $lastName;

    /**
     * E-Mail.
     *
     * @var string
     * @validate NotEmpty
     * @validate EmailAddress
     */
    protected $email;

    /**
     * Phone.
     *
     * @var string
     */
    protected $phone;

    /**
     * Street.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $street;

    /**
     * House number.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $houseNumber;

    /**
     * ZIP.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $zip;

    /**
     * City.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $city;

    /**
     * Country.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $country;

    /**
     * Message.
     *
     * @var string
     */
    protected $message;

    /**
     * Get first name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set first name.
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set last name.
     *
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Get E-Mail.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set E-Mail.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street.
     *
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * Get house number.
     *
     * @return string
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     * Set house number.
     *
     * @param string $houseNumber
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * Get ZIP.
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set ZIP.
     *
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set city.
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country.
     *
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
