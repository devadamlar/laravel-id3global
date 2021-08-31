<?php

namespace DevAdamlar\LaravelId3global\Tests;

use DateTime;
use DevAdamlar\LaravelId3global\Traits\Verifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;

class User extends Model
{
    use Verifiable, WithFaker;

    public ?string $email;
    public ?string $first_name;
    public ?string $last_name;
    public ?DateTime $birthday;
    public ?string $sex;
    public ?string $street;
    public ?string $post_code;
    public ?string $city;
    public ?string $country;

    private array $verifiables = [
        'Personal.PersonalDetails.Gender' => 'sex',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setUpFaker();

        $this->email = $this->faker->email;
        $this->first_name = $this->faker->firstName;
        $this->last_name = $this->faker->lastName;
        $this->birthday = $this->faker->dateTime;
        $this->sex = $this->faker->randomElement(['male', 'female']);
        $this->street = $this->faker->streetAddress;
        $this->post_code = $this->faker->postcode;
        $this->city = $this->faker->city;
        $this->country = $this->faker->country;
    }
}