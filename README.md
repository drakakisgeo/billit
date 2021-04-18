# Billit client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]

This PHP client can be used to connect with the [billit.io API](https://www.billit.io/docs) .

*Note: Billit.io also has an integration with zapier.com, Contact us in order to give you access to our beta zapier app.*

## Install

### Via Composer

``` bash
$ composer require drakakisgeo/billit
```

### Laravel Framework
If you use the Laravel Framework you need to provide your Billit API key and Base url, so add a key to your config/services.php file as:
``` php
'billit' => [
        'token' => env('BILLIT_API_TOKEN'),
        'sandbox' => env('BILLIT_API_IS_SANDBOX'),
        'version' => 'v1'
    ]
```
To initialize the client is just a call as this:

``` php
$client = resolve('billit');
```
You can of course also use the Billit Facade like so

``` php
Billit::myAccount();
```

## Examples
##### List customers

``` php
use Drakakisgeo\Billit\Billit;

$client = new Billit('yourToken');
$client->customers();
```

##### Get Account details

``` php
$client->myAccount();
```

##### Get Customers

``` php
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

##### Update customer

``` php
$client->customerUpdate([
    'customerType' => 'company',
    'company' => 'Awesome S.A.',
    'inCharge' => 'Kalampakopoulos George',
    'lang' => 'el',
    'VatId'=>'075101010'
]);
```

##### Get customer

``` php
$client->customerShow(1010);
```

##### Delete customer

``` php
$client->customerDelete(1010);
```

##### Create invoice
A full rather complicated example, MyData enabled

``` php
$client->invoiceCreate([
    "customerId"=>694,
    "sendMail"=> false,
    "excludeMydata"=> true,
    "invoiceDate"=>"2021-04-15",
    "invoiceTypeId"=>349,
    "isPaid" => true,
    "mydataInvoiceType" => "2.1",
    "taxes"=> [
        [
            "taxId"=> 514
        ],
        [
            "taxId"=> 524,
            "taxVatShow"=> 1
        ]
    ],
    "products"=> [
        [
            "productId"=> null,
            "details"=> "test",
            "measurementUnit"=> 1,
            "vatId" => 376,
            "price" => 123.32,
            "quantity"=> 1,
            "incomeClassificationCat"=>"category1_3",
            "incomeClassificationType"=>"E3_561_001"
        ]
    ],
    "tags" => ["billit","test-tag"],
    "mydataPayment"=>[
        "paidAmount"=> 0.32,
        "paymentMethod"=> 3,
        "epiPistosi"=> 10
    ]
]
);
```

Check the rest of the methods to src/Billit.php and the [official API](https://billit.io/docs) regarding the options that can be used in each case.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- [Drakakis George][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/drakakisgeo/billit.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/drakakisgeo/billit/master.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/drakakisgeo/billit
[link-travis]: https://travis-ci.org/drakakisgeo/billit
[link-author]: https://github.com/drakakisgeo
[link-contributors]: ../../contributors