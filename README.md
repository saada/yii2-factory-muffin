# Yii2 Extension for [Factory Muffin](https://github.com/thephpleague/factory-muffin)

[![Version](https://img.shields.io/packagist/v/saada/yii2-factory-muffin.svg?style=flat-square)](Version)
[![Total Downloads](https://img.shields.io/packagist/dt/saada/yii2-factory-muffin.svg?style=flat-square)](https://packagist.org/packages/saada/yii2-factory-muffin) [![Software License](https://img.shields.io/badge/license-MIT-orange.svg?style=flat-square)](LICENSE)

I found fixtures really tiring and cumbersome to maintain with Yii2 models. So, I decided to write this extension
that basically wraps FactoryMuffin and attaches factory definitions to any model that you want to seed in the database.
I found it extremely helpful for seeding databases dynamically which is especially useful when writing tests.
If you've used FactoryGirl or FactoryMuffin before, this is the same concept tailored for the Yii2 framework.
I tried to keep things as tidy as possible. Contributions are more than welcome!

## Installing

[PHP](https://php.net) 5.5+ and [Composer](https://getcomposer.org) are required.

In your composer.json, simply add these packages to your `"require"` section

```json
{
    "require": {
        "saada/yii2-factory-muffin": "~2.0",
        "league/factory-muffin-faker": "~2.0"
    }
}
```

## Example Usage

```php
<?php
$fm = new saada\FactoryMuffin\FactoryMuffin();
$fm->define('models\Message')->setDefinitions([
    'user_id'      => 'factory|models\User',
    'subject'      => 'sentence',
    'message'      => 'text',
    'phone_number' => 'randomNumber|8',
    'created'      => 'date|Ymd h:s',
    'slug'         => 'call|makeSlug|word',
], function ($object, $saved) {
    // we're taking advantage of the callback functionality here
    $object->message .= '!';
});

$fm->define('models\User')->setDefinitions([
    'username' => 'firstNameMale',
    'email'    => 'email',
    'avatar'   => 'imageUrl|400;600',
    'greeting' => RandomGreeting::get(),
    'four'     => function() {
        return 2 + 2;
    },
]);
```

All the `FactoryMuffin` API calls are accessible from the `$fm` instance above.

## Alternative In-Model Pattern (Optional)
This pattern is a convenience that you DO NOT HAVE TO use in your project. But I found really useful since a lot of our model definitions for tests and fake data are heavily coupled with our application models.

First, we need to implement `FactoryInterface` interfaces to our models.

```php
use saada\FactoryMuffin\FactoryInterface;
use League\FactoryMuffin\Faker\Facade as Faker;

class Message extends ActiveRecord implements FactoryInterface
{
    //...
    public function definitions() {
        return [
            [
                'user_id'      => 'factory|'.User::class,
                'subject'      => Faker::sentence(),
                'message'      => Faker::text(),
                'phone_number' => Faker::randomNumber(8),
                'created'      => Faker::date('Ymd h:s'),
                'slug'         => self::class . '::makeSlug',
            ],
            function($object, $saved) {
                // we're taking advantage of the callback functionality here
                $object->message .= '!';
            }
        ];
    }
    //...
}

// and here's my interpretation of what a Yii2 model would look like
class User extends ActiveRecord implements IdentityInterface, FactoryInterface
{
    //...
    public function definitions() {
        $security = Yii::$app->getSecurity();
        return [
            [
                'first_name'           => Faker::firstName(),
                'last_name'            => Faker::lastName(),
                'phone'                => Faker::phoneNumber(),
                'email'                => Faker::email(),
                'auth_key'             => $security->generateRandomString(),
                'password_hash'        => $security->generatePasswordHash('MyFixedTestUserPassword'),
                'password_reset_token' => $security->generateRandomString() . '_' . time(),
            ]
        ];
    }
    //...
}
```

Now that we have our models, we can now start seeding our database from within our tests

```php
use saada\FactoryMuffin\FactoryMuffin;
class MuffinTest extends TestCase {
    //...
    public function testCreateFiveMessages()
    {
        //init factory muffin
        $fm = new FactoryMuffin();
        $fm->loadModelDefinitions([Message::class, User::class]);

        // alternatively you can pass the same array to the constructor
        $fm = new FactoryMuffin([Message::class, User::class]);

        //seed database with 5 messages add set some custom attributes.
        $messages = $fm->seed(5, Message::class, ['created_by' => 1, 'company_id' => 1]);
        Debug::debug($messages);
        $this->assertNotEmpty($messages);

        // confirm that users were created for each message
        foreach ($messages as $message) {
            $this->assertInstanceOf(User::class, $message->user);
        }
    }
}
```

### TODO

- [x] Create wrapper around Factory Muffin 3.0
- [ ] Create Gii generator to automagically add a generic definitions() implementation to a model
