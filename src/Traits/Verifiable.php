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

        $personalDetails = $this->makePersonalDetails($overrides['Personal']['PersonalDetails'] ?? []);
        $addresses = $this->makeAddressContainer($overrides['Addresses'] ?? []);
        $contactDetails = $this->makeContactDetails($overrides['ContactDetails'] ?? []);

        $identity = new Identity();
        $identity->setPersonalDetails($personalDetails)->setAddresses($addresses)->setContactDetails($contactDetails);

        return $identity;
    }

    private function makePersonalDetails(array $overrides): PersonalDetails
    {
        $personalDetails = new PersonalDetails();
        $personalDetails
            ->setTitle($this->getField('Title', 'title', $overrides))
            ->setForename($this->getField('Forename', 'first_name', $overrides))
            ->setMiddleName($this->getField('MiddleName', 'middle_name', $overrides))
            ->setSurname($this->getField('Surname', 'last_name', $overrides))
            ->setGender($this->getField('Gender', 'gender', $overrides))
            ->setDateOfBirth($this->getField('DateOfBirth', 'birthday', $overrides))
            ->setCountryOfBirth($this->getField('CountryOfBirth', 'birth_country', $overrides));

        return $personalDetails;
    }

    private function makeAddressContainer(array $overrides): AddressContainer
    {
        $currentAddressOverrides = $overrides['CurrentAddress'] ?? [];

        $currentAddress = new FixedFormatAddress();
        $currentAddress
            ->setStreet($this->getField('Street', 'street', $currentAddressOverrides))
            ->setZipPostcode($this->getField('ZipPostcode', 'post_code', $currentAddressOverrides))
            ->setCity($this->getField('City', 'city', $currentAddressOverrides))
            ->setCountry($this->getField('Country', 'country', $currentAddressOverrides));

        $addressContainer = new AddressContainer();

        $addressContainer->setCurrentAddress($currentAddress);

        return $addressContainer;
    }

    private function makeContactDetails(array $overrides): ContactDetails
    {
        $contactDetails = new ContactDetails();
        $contactDetails->setEmail($this->getField('Email', 'email', $overrides));

        return $contactDetails;
    }

    private function getField(string $name, string $default, array $override)
    {
        if (array_key_exists($name, $override)) {
            return $override[$name];
        }
        if (array_key_exists($name, $this->verifiables)) {
            return $this->{$this->verifiables[$name]};
        }

        return $this->$default;
    }
}