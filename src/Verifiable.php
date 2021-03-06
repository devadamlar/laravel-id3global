<?php

namespace DevAdamlar\LaravelId3global;

use ID3Global\Identity\Address\AddressContainer;
use ID3Global\Identity\Address\FixedFormatAddress;
use ID3Global\Identity\ContactDetails;
use ID3Global\Identity\Identity;
use ID3Global\Identity\PersonalDetails;

/**
 * Trait Id3globalUser.
 *
 * @property array $inputData The array keys should correspond to the properties of `GlobalInputData` class
 *                            specified in ID3global's WSDL documentation. The array should be flat.
 *                            You can use a dot separator to specify the nested properties.
 */
trait Verifiable
{
    private array $overrides;

    /**
     * Makes an Identity object from the Eloquent to be sent to the ID3global API.
     *
     * @param array $overrides The array structure should correspond to the `GlobalInputData` class
     *                         specified in ID3global's WSDL documentation
     *
     * @return Identity
     */
    public function makeInputData(array $overrides = []): Identity
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
            ->setTitle($this->getValue($keyPrefix.'Title', 'title'))
            ->setForename($this->getValue($keyPrefix.'Forename', 'first_name'))
            ->setMiddleName($this->getValue($keyPrefix.'MiddleName', 'middle_name'))
            ->setSurname($this->getValue($keyPrefix.'Surname', 'last_name'))
            ->setGender($this->getValue($keyPrefix.'Gender', 'gender'))
            ->setDateOfBirth($this->getValue($keyPrefix.'DateOfBirth', 'birthday'))
            ->setCountryOfBirth($this->getValue($keyPrefix.'CountryOfBirth', 'birth_country'));

        return $personalDetails;
    }

    private function makeAddressContainer(): AddressContainer
    {
        $keyPrefix = 'Addresses.';

        $currentAddress = new FixedFormatAddress();
        $currentAddress
            ->setStreet($this->getValue($keyPrefix.'CurrentAddress.Street', 'street'))
            ->setZipPostcode($this->getValue($keyPrefix.'CurrentAddress.ZipPostcode', 'post_code'))
            ->setCity($this->getValue($keyPrefix.'CurrentAddress.City', 'city'))
            ->setCountry($this->getValue($keyPrefix.'CurrentAddress.Country', 'country'));

        $addressContainer = new AddressContainer();

        $addressContainer->setCurrentAddress($currentAddress);

        return $addressContainer;
    }

    private function makeContactDetails(): ContactDetails
    {
        $keyPrefix = 'ContactDetails.';

        $landlineNumber = $this->getValue($keyPrefix.'LandTelephone.Number', 'landline');
        $mobileNumber = $this->getValue($keyPrefix.'MobileTelephone.Number', 'mobile');
        $workNumber = $this->getValue($keyPrefix.'WorkTelephone.Number', 'work_phone');

        $landline = new ContactDetails\PhoneNumber();
        $mobile = new ContactDetails\PhoneNumber();
        $workPhone = new ContactDetails\PhoneNumber();

        $landline->setNumber($landlineNumber);
        $mobile->setNumber($mobileNumber);
        $workPhone->setNumber($workNumber);

        $contactDetails = new ContactDetails();
        $contactDetails
            ->setEmail($this->getValue($keyPrefix.'Email', 'email'))
            ->setLandTelephone($landline)
            ->setMobileTelephone($mobile)
            ->setWorkTelephone($workPhone);

        return $contactDetails;
    }

    private function getValue(string $fieldName, string $defaultAttribute)
    {
        if (array_key_exists($fieldName, $this->overrides)) {
            return $this->overrides[$fieldName];
        }
        if (array_key_exists($fieldName, $this->inputData)) {
            $attributeTree = explode('.', $this->inputData[$fieldName]);
            $value = $this;

            foreach ($attributeTree as $key => $attribute) {
                $value = $value->{$attribute} ?? null;
            }

            return $value;
        }

        return $this->$defaultAttribute;
    }
}
