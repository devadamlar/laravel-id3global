<?php

namespace DevAdamlar\LaravelId3global\Tests;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;

class Contact extends Model
{
    use WithFaker;

    public string $email;
    public string $mobile;
    private string $landline;
    private string $work_phone;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setUpFaker();

        $this->email = $this->faker->email;
        $this->mobile = $this->faker->phoneNumber;
        $this->landline = $this->faker->phoneNumber;
        $this->work_phone = $this->faker->phoneNumber;
    }
}