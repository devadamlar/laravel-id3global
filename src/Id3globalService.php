<?php

namespace DevAdamlar\LaravelId3global;

use ID3Global\Exceptions\IdentityVerificationFailureException;
use ID3Global\Identity\Identity;
use ID3Global\Service\GlobalAuthenticationService;
use ID3Global\Stubs\Gateway\GlobalAuthenticationGatewayFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static setProfileId(string $profileId)
 * @method static setProfileVersion(int $profileVersion)
 * @method static verifyIdentity(Identity $identity, ?string $customerReference)
 */
class Id3globalService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GlobalAuthenticationService::class;
    }

    /**
     * Replace the bound instance with a fake.
     *
     * @param string $bandText
     * @param int    $score
     *
     * @return GlobalAuthenticationService
     */
    public static function fake(string $bandText = 'PASS', int $score = 3000): GlobalAuthenticationService
    {
        $gateway = new GlobalAuthenticationGatewayFake('username', 'password');
        $gateway->setBandText($bandText)->setScore($score);

        self::swap($service = new GlobalAuthenticationService($gateway));

        return $service;
    }

    /**
     * Sends an AuthenticateSP request.
     *
     * @param Identity $identity          Represents GlobalInputData
     * @param string   $profileId         The ID of the profile you want to use. Login to the ID3global's GlobalAdmin page to get the IDs.
     * @param int      $profileVersion    Version of the profile to be used. The latest version will be used if not specified.
     * @param ?string  $customerReference
     *
     * @throws IdentityVerificationFailureException
     *
     * @return string
     */
    public static function authenticateSp(Identity $identity, string $profileId, int $profileVersion = 0, ?string $customerReference = null): string
    {
        /** @var GlobalAuthenticationService $service */
        $service = self::setProfileId($profileId)->setProfileVersion($profileVersion);

        return $service->verifyIdentity($identity, $customerReference);
    }
}
