# Install
```bash
composer require shelamkoff/currency-rate-parser
````
# Usage
```php

$data = (new Parser())->parse(Currency::RUB, Currency::USD);

array:5 [▼
  "currencyFrom" => "RUB"
  "currencyTo" => "USD"
  "rate" => 0.0133959
  "reverseRate" => 74.6495
  "actually" => DateTime @1612627961 {#1452 ▶}
]
````
# Supported currency codes
```php
Currency::getCodes();
````
