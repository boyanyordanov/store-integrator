# CloudCart Store Integrator Package

## Goals

- Allow seamless integrations between CloudCart's services and other popular platforms.

## Current Support

- Ebay mostly (not production ready)
- Amazon (initial level)

## Configuration 

### For eBay

The configuration can be passed directly to the eBay SDK before the integrator is composed, or preferably they can be
taken from environment variables.

***List of variables for eBay***

- EBAY-TRADING-API-VERSION - The version of the eBay API.
- EBAY-DEV-ID - The Developer ID key. Taken from eBay.
- EBAY-APP-ID - App ID key, again taken from eBay. Used to identify CloudCart before the users.
- EBAY-CERT-ID - Another Key taken from eBay.

## Usage

Currently the Integrator objects need to be instantiated directly.
To reduce complexity the services are grouped in several smaller API wrapper classes, which are then used
to compose the main Integrator class.

In future this will be hidden from the end user of the package.

```
    $integrator = new StoreIntegrator\Factory([
        'ebay' => [
            'userToken' => 'token'
            // more data needed 
        ]
    ]);
    
    $integrator->provider('ebay')->products->getProducts();
    
    $integrator->provider('ebay')->factory->makeShippingService($data = []);
    $integrator->provider('ebay')->factory->makeProduct($data = []);
    $integrator->provider('ebay')->factory->makeProduct($data = []);
   
```

Added methdos to get user tokens with a few requirements to the application using the package.

Flow;

1 . Get an instance of the eBay provider

```
    $integrator = new StoreIntegrator\Factory();
    
    $ebay = $integrator->provider('ebay', [ ... ]);
    
```

2 . Set the RuName of the app (special identificator from eBay for the application)

```
    $ebay->auth->setRuName($ruName);
```

3 . Get a special session id from eBay and store it somewhere (e.g. in the session)

```
    $sessionId = $ebay->auth->getSessionID();
    
    // save the session id
```

4 . Build a special eBay url to show the user a dialog, where they can agree

```
    $url = $ebay->auth->buildRedirectUrl($sessionId);
```

5 . Redirect the user to the built url (or leave it as link on a page somewhere)

6 . If the user agrees eBay redirects them to a predefined url in the application (https is mandatory) (same happens when they do not agree, but for another url)

7 . When the redirect from eBay is received, get the token and save it for future use

```
    $token = $ebay->auth->getToken($sessionId);
    
    // Save the token
```


## Limitations

- eBay does not allow variation images for multiple properties. Currently accepts only images on product (parent for variations) level

## TODO

- Factories to simplify initialization (almost done)
- Editing an item on eBay (it's better done with multiple requests [docs](http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/ReviseFixedPriceItem.html#ReviseFixedPriceItem))
- Some methods return the raw responses from eBay (would be better with custom data objects)
- Defining interfaces for the data objects to use them as bridge between the domain model of the application and the integrator for ease of use.
- Pretty much everything for Amazon

- Add token expiration logic [docs] (http://developer.ebay.com/devzone/guides/ebayfeatures/Basics/Tokens-About.html)

## Contributors

* Boyan Yordanov [b.yordanov@cloudcart.com](mailto:b.yordanov@cloudcart.com)