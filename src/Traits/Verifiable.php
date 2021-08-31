<?php

namespace DevAdamlar\LaravelId3global\Traits;

use Exception;
use ID3Global\Gateway\GlobalAuthenticationGateway;
use ID3Global\Identity\Address\AddressContainer;
use ID3Global\Identity\Address\FixedFormatAddress;
use ID3Global\Identity\ContactDetails;
use ID3Global\Identity\Identity;
use ID3Global\Identity\PersonalDetails;
use ID3Global\Service\GlobalAuthenticationService;
use Illuminate\Support\Facades\App;
use stdClass;

trait Verifiable
{
    private array $overrides;

    /**
     * Sends an AuthenticateSP request
     *
     * @param string $profileId
     * @param array $overrides
     * @return stdClass AuthenticateSPResponse
     *
     * @throws Exception
     */
    public function verify(string $profileId, array $overrides = []): stdClass
    {
        $gateway = App::make(GlobalAuthenticationGateway::class);

        $identity = $this->makeIdentity($overrides);

        $service = new GlobalAuthenticationService($identity, $profileId, $gateway);
        $service->verifyIdentity();

        return $service->getLastVerifyIdentityResponse();
    }

    /**
     * Makes an Identity object to be sent to the ID3global API
     *
     * @param array $overrides
     * @return Identity
     */
    public function makeIdentity(array $overrides = []): Identity
    {
        $this->overrides = $overrides;

        $personalDetails = $this->makePersonalDetails();
        $addresses = $this->makeAddressContainer();
        $contactDetails = $this->makeContactDetails();

        $identity = new Identity();
        $identity->setPersonalDetails($personalDetails)->setAddresses($addresses)->setContactDetails($contactDetails);

        return $identity;
    }

    private function makePersonalDetails(): PersonalDetails
    {
        $keyPrefix = 'Personal.PersonalDetails.';
        $personalDetails = new PersonalDetails();
        $personalDetails
            ->setTitle($this->getValue($keyPrefix . 'Title', 'title'))
            ->setForename($this->getValue($keyPrefix . 'Forename', 'first_name'))
            ->setMiddleName($this->getValue($keyPrefix . 'MiddleName', 'middle_name'))
            ->setSurname($this->getValue($keyPrefix . 'Surname', 'last_name'))
            ->setGender($this->getValue($keyPrefix . 'Gender', 'gender'))
            ->setDateOfBirth($this->getValue($keyPrefix . 'DateOfBirth', 'birthday'))
            ->setCountryOfBirth($this->getValue($keyPrefix . 'CountryOfBirth', 'birth_country'));

        return $personalDetails;
    }

    private function makeAddressContainer(): AddressContainer
    {
        $keyPrefix = 'Addresses.';

        $currentAddress = new FixedFormatAddress();
        $currentAddress
            ->setStreet($this->getValue($keyPrefix . 'CurrentAddress.Street', 'street'))
            ->setZipPostcode($this->getValue($keyPrefix . 'CurrentAddress.ZipPostcode', 'post_code'))
            ->setCity($this->getValue($keyPrefix . 'CurrentAddress.City', 'city'))
            ->setCountry($this->getValue($keyPrefix . 'CurrentAddress.Country', 'country'));

        $addressContainer = new AddressContainer();

        $addressContainer->setCurrentAddress($currentAddress);

        return $addressContainer;
    }

    private function makeContactDetails(): ContactDetails
    {
        $keyPrefix = 'ContactDetails.';
        $contactDetails = new ContactDetails();
        $contactDetails->setEmail($this->getValue($keyPrefix . 'Email', 'email'));

        return $contactDetails;
    }

    private function getValue(string $fieldName, string $defaultAttribute)
    {
        if (array_key_exists($fieldName, $this->overrides)) {
            return $this->overrides[$fieldName];
        }
        if (array_key_exists($fieldName, $this->verifiables)) {
            return $this->{$this->verifiables[$fieldName]};
        }

        return $this->$defaultAttribute;
    }
}