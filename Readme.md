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
    $productWrapper = new ProductWrapper($token);
    $categoriesWrapper = new CategoriesWrapper($token);
    $detailsWrapper = new DetailsWrapper($token);
    
    $integrator = new EbayProductIntegrator($productWrapper, $categoriesWrapper, $detailsWrapper);
    
    $products = $integrator->getProducts();
    
    foreach($products as $product) {
        // Do stuff with the product
    }
```
## Limitations

- eBay does not allow variation images for multiple properties. Currently accepts only images on product (parent for variations) level

## Contributors

* Boyan Yordanov [b.yordanov@cloudcart.com](mailto:b.yordanov@cloudcart.com)