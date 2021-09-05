## Introduction

This package converts Eloquent models to an Identity object that can be sent to verify users through [ID3global API](https://www.gbgplc.com/apac/products/id-verification/).

## Installation

Install the library:

```shell
composer require devadamlar/laravel-id3global
```

Define the following environment variables:

```dotenv
ID3GLOBAL_USERNAME=
ID3GLOBAL_PASSWORD=
```

The pilot site will be used if the `APP_ENV` is different than `Production`. You can override this by setting the `ID3GLOBAL_USE_PILOT` variable in the environment file.

## Usage

Extend your Eloquent models from `Id3globalUser` instead of `User`.
You can now call `authenticateSp` method on that model to do a verification. If you need to make a `GlobalInputData` from the Eloquent without sending a request, just call the `makeIdentity` method on the model.

You can override the `$authenticateSpFields` property to map the model's attributes to the ID3global's `GlobalInputData` properties.
If you want to map an attribute from a relationship, put the name of the relationship, and the attribute separated by a dot:

```php
class User extends \DevAdamlar\LaravelId3global\Id3globalUser
{
    protected array $authenticateSpFields = [
        'Personal.PersonalDetails.Gender' => 'sex',
        'ContactDetails.MobileTelephone.Number' => 'contact.mobile',
    ];
    
    public function contact(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contact::class);
    }
}

class Contact extends \Illuminate\Database\Eloquent\Model
{

}
```
Some commonly-used attributes will be automatically mapped to the corresponding fields:

| `GlobalInputData` property               | Corresponding model attribute |
| ---------------------------------------- | ----------------------------- |
| `Personal.PersonalDetails.Forename`      | `first_name`                  |
| `Personal.PersonalDetails.MiddleName`    | `middle_name`                 |
| `Personal.PersonalDetails.Surname`       | `last_name`                   |
| `Personal.PersonalDetails.Gender`        | `gender`                      |
| `Personal.PersonalDetails.DateOfBirth`   | `birthday`                    |
| `Personal.PersonalDetails.CountryOfBirth`| `birth_country`               |
| `Addresses.CurrentAddress.Street`        | `street`                      |
| `Addresses.CurrentAddress.ZipPostcode`   | `post_code`                   |
| `Addresses.CurrentAddress.City`          | `city`                        |
| `Addresses.CurrentAddress.Country`       | `country`                     |
| `ContactDetails.Email`                   | `email`                       |
| `ContactDetails.LandTelephone.Number`    | `landline`                    |
| `ContactDetails.MobileTelephone.Number`  | `mobile`                      |
| `ContactDetails.WorkTelephone.Number`    | `work_phone`                  |

If you need to override some properties on the fly, pass an array with the overridden properties as a value to the corresponding keys:

```php
$user = User::find(1);
$user->makeInputData([
    'ContactDetails.MobileTelephone.Number' => '+994502000000'
]);
```

Refer to the [ID3global's WSDL documentation](http://www.id3globalsupport.com/Website/content/Web-Service/WSDL%20Page/WSDL%20HTML/ID3%20Global%20WSDL-%20Live.xhtml) to see the structure of the `GlobalInputData` class.