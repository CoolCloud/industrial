Industrial Dependency Injection Framework for PHP 5.3
=====================================================
Industrial is a dependency injection framework for PHP 5.3 inspired by Google 
Guice. 

Getting Started
---------------
The recommended way to use Industrial is through [composer](http://getcomposer.org). 
Add Industrial to your project's ``composer.json`` file:
    
    {
        "require": {
            "pihi/industrial": "*"
        }
    }

Find out more about Composer installation and use, along with other best
practices at http://getcomposer.org/doc/00-intro.md


Basic Usage
-----------
Setting up modules:

```php
<?php

/**
 * A Module should will releated bindings
 */
class PaymentModule extends \Industrial\Module
{
    protected function config()                
    {
        $this->bind('ITaxCalculator')->to('OklahomaStateSalesTax');
        $this->bind('IPaymentProcessor')->to('PayPalProcessor');
    }
}

/**
 * Use named bindings to bind the same interface multiple times
 */
class ShippingModule extends \Industrial\Module
{
    protected function config()
    {
        $this->bind('IShippingCalculator')
            ->to('FedExShippingCalculator')->named('FedEx');

        $this->bind('IShippingCalculator')
            ->to('UPSShippingCalculator')->named('UPS');

        $this->bind('IShippingCalculator')
            ->to('USPSShippingCalculator')->named('USPS');
    }
}

/**
 * Call using() to provide a method which constructs the bound object
 */
class CartModule extends \Industrial\Module
{
    protected function config()
    {
        $factory = $this->factory;
        $this->bind('ShoppingCart')->using(
            function () use ($factory) {
                $request = $factory->make('Request');
                return $request->getUser()->getShoppingCart();
            });
    }
}
?>
```

Instantiate and use factory:
```php
<?php
    
$factory = new \Industrial\Factory(
    new CartModule,
    new ShippingModule,
    new PaymentModule);


// Create an object using a binding
$taxCalculator = $factory->make('ITaxCalculator');

// Create an object using a named binding
$shippingCalculator = $factory->make('IShippingCalculator', 'FedEx');

// Use with to provide constructor arguments that won't be provided by 
// Just-In-Time bindings
$paymentProcessor = $factory->with(array('apikey'=>'paypal_api_key'))
                            ->make('IPaymentProcessor');
?>
```


