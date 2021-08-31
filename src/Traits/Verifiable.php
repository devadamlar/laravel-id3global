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
    /**
     * Sends an AuthenticateSP request
     *
     * @param string $profileId
     *
     * @return stdClass AuthenticateSPResponse
     *
     * @throws Exception
     */
    public function verify(string $profileId): stdClass
    {
        $gateway = App::make(GlobalAuthenticationGateway::class);

        $identity = $this->makeIdentity();

        $service = new GlobalAuthenticationService($identity, $profileId, $gateway);
        $service->verifyIdentity();

        return $service->getLastVerifyIdentityResponse();
    }

    /**
     * Makes an Identity object to be sent to the ID3global API
     *
     * @return Identity
     */
    public function makeIdentity(): Identity
    {
        $personalDetails = $this->makePersonalDetails();
        $addresses = $this->makeAddressContainer();
        $contactDetails = $this->makeContactDetails();

        $identity = new Identity();
        $identity->setPersonalDetails($personalDetails)->setAddresses($addresses)->setContactDetails($contactDetails);

        return $identity;
    }

    private function makePersonalDetails(): PersonalDetails
    {
        $personalDetails = new PersonalDetails();
        $personalDetails
            ->setTitle($this->getField('Title', 'title'))
            ->setForename($this->getField('Forename', 'first_name'))
            ->setMiddleName($this->getField('MiddleName', 'middle_name'))
            ->setSurname($this->getField('Surname', 'last_name'))
            ->setGender($this->getField('Gender', 'gender'))
            ->setDateOfBirth($this->getField('DateOfBirth', 'birthday'))
            ->setCountryOfBirth($this->getField('CountryOfBirth', 'birth_country'));

        return $personalDetails;
    }

    private function makeAddressContainer(): AddressContainer
    {
        $currentAddress = new FixedFormatAddress();
        $currentAddress
            ->setStreet($this->getField('Street', 'street'))
            ->setZipPostcode($this->getField('ZipPostcode', 'post_code'))
            ->setCity($this->getField('City', 'city'))
            ->setCountry($this->getField('Country', 'country'));

        $addressContainer = new AddressContainer();

        $addressContainer->setCurrentAddress($currentAddress);

        return $addressContainer;
    }

    private function makeContactDetails(): ContactDetails
    {
        $contactDetails = new ContactDetails();
        $contactDetails->setEmail($this->getField('Email', 'email'));

        return $contactDetails;
    }

    private function getField(string $name, string $default)
    {
        if (array_key_exists($name, $this->verifiables)) {
            return $this->{$this->verifiables[$name]};
        }

        return $this->$default;
    }
}