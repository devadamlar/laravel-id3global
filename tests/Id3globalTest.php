<?php

namespace DevAdamlar\LaravelId3global\Tests;

use Carbon\Carbon;
use DevAdamlar\LaravelId3global\Id3globalService;
use ID3Global\Exceptions\IdentityVerificationFailureException;
use ID3Global\Identity\Address\AddressContainer;
use ID3Global\Identity\Address\FixedFormatAddress;
use ID3Global\Identity\Identity;
use ID3Global\Identity\PersonalDetails;
use ID3Global\Stubs\Gateway\GlobalAuthenticationGatewayFake;
use Orchestra\Testbench\TestCase;

class Id3globalTest extends TestCase
{
    /**
     * @var User
     */
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $birthday = Carbon::parse($this->user->birthday);

        $identity = new Identity();

        $personalDetails = new PersonalDetails();
        $personalDetails
            ->setForename($this->user->first_name)
            ->setSurname($this->user->last_name)
            ->setGender($this->user->sex)
            ->setDateOfBirth($birthday);

        $currentAddress = new FixedFormatAddress();
        $currentAddress
            ->setStreet($this->user->street)
            ->setCity($this->user->city)
            ->setCountry($this->user->country)
            ->setZipPostcode($this->user->post_code);
        $addresses = new AddressContainer();

        $identity->setPersonalDetails($personalDetails)->setAddresses($addresses);
    }

    protected function getPackageProviders($app): array
    {
        return [
            'DevAdamlar\LaravelId3global\ServiceProvider',
        ];
    }

    /**
     * @throws IdentityVerificationFailureException
     */
    public function test_verify_returns_valid_response()
    {
        // Arrange
        Id3globalService::fake();

        // Act
        $response = Id3globalService::authenticateSp($this->user->makeInputData(), 'profile-id');

        // Assert
        $this->assertSame(GlobalAuthenticationGatewayFake::IDENTITY_BAND_PASS, $response);
    }

    public function test_make_identity_creates_identity_with_correct_properties()
    {
        // Act
        $identity = $this->user->makeInputData();

        // Assert
        $this->assertNull($identity->getPersonalDetails()->getTitle());
        $this->assertSame($this->user->first_name, $identity->getPersonalDetails()->getForename());
        $this->assertNull($identity->getPersonalDetails()->getMiddleName());
        $this->assertSame($this->user->last_name, $identity->getPersonalDetails()->getSurname());
        $this->assertSame($this->user->sex, $identity->getPersonalDetails()->getGender());
        $this->assertSame($this->user->birthday, $identity->getPersonalDetails()->getDateOfBirth());
        $this->assertSame($this->user->street, $identity->getAddresses()->getCurrentAddress()->getStreet());
        $this->assertSame($this->user->post_code, $identity->getAddresses()->getCurrentAddress()->getZipPostcode());
        $this->assertSame($this->user->city, $identity->getAddresses()->getCurrentAddress()->getCity());
        $this->assertSame($this->user->country, $identity->getAddresses()->getCurrentAddress()->getCountry());
    }

    public function test_override_identity_properties()
    {
        // Act
        $identity = $this->user->makeInputData([
            'Personal.PersonalDetails.CountryOfBirth' => 'Birth Country',
            'Addresses.CurrentAddress.Country'        => 'Overridden Country',
            'ContactDetails.Email'                    => 'test@email.com',
        ]);

        // Assert
        $this->assertSame('Birth Country', $identity->getPersonalDetails()->getCountryOfBirth());
        $this->assertSame('Overridden Country', $identity->getAddresses()->getCurrentAddress()->getCountry());
        $this->assertSame('test@email.com', $identity->getContactDetails()->getEmail());
    }

    public function test_relationship_fields()
    {
        // Arrange
        $contact = new Contact();
        $this->user->setRelation('contact', $contact);

        // Act
        $identity = $this->user->makeInputData();

        // Assert
        $this->assertSame($contact->mobile, $identity->getContactDetails()->getMobileTelephone()->getNumber());
    }
}
