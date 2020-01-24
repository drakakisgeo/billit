# Billit client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]

The goal is to make the [billit.io API](https://www.billit.io/docs) even easier to work with if you are a PHP developer. Check the examples how easy it is to work with this client and update your resources in your billit account.

Note: Billit.io also has an integration with zapier.com, Contact me in order to give you access to our beta zapier app.

## Install

### Via Composer

``` bash
$ composer require drakakisgeo/billit
```

### Laravel Framework
If you use the Laravel Framework you need to provide your Billit API key, so add a key to your config/services.php file as:
``` php
'billit'=> env('BILLIT_TOKEN')
```
To initialize the client is just a call as this:

``` php
$client = resolve('billit');
```

## Examples
##### List customers

``` php
use Drakakisgeo\Billit\Billit;

$client = new Billit('yourToken');
$client->customers();
```

##### Create customer

``` php
$client->customerCreate([
    'customerType' => 'company',
    'company' => 'Awesome S.A.',
    'inCharge' => 'Kalampakopoulos George',
    'lang' => 'el',
    'VatId'=>'075101010'
]);
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- [Drakakis George][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/drakakisgeo/mailtester.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/drakakisgeo/mailtester/master.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/drakakisgeo/mailtester
[link-travis]: https://travis-ci.org/drakakisgeo/mailtester
[link-author]: https://github.com/drakakisgeo
[link-contributors]: ../../contributors