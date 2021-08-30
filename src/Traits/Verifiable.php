<?php

namespace DevAdamlar\LaravelId3global\Traits;


use ID3Global\Gateway\GlobalAuthenticationGateway;
use ID3Global\Identity\Address\AddressContainer;
use ID3Global\Identity\ContactDetails;
use ID3Global\Identity\Identity;
use ID3Global\Identity\PersonalDetails;
use ID3Global\Service\GlobalAuthenticationService;
use Illuminate\Support\Facades\App;
use stdClass;

trait Verifiable
{
    public function verify(string $profileId): stdClass
    {
        $gateway = App::make(GlobalAuthenticationGateway::class);

        $identity = $this->makeIdentity();

        $service = new GlobalAuthenticationService($identity, $profileId, $gateway);
        $service->verifyIdentity();

        return $service->getLastVerifyIdentityResponse();
    }

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
        return new PersonalDetails();
    }

    private function makeAddressContainer(): AddressContainer
    {
        return new AddressContainer();
    }

    private function makeContactDetails(): ContactDetails
    {
        return new ContactDetails();
    }
}