<?php

namespace DevAdamlar\LaravelId3global\Tests;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;

class Contact extends Model
{
    use WithFaker;

    public ?int $user_id;
    public string $email;
    public string $mobile;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setUpFaker();

        $this->email = $this->faker->email;
        $this->mobile = $this->faker->phoneNumber;
    }
}