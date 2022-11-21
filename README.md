# StoreAPI

Two simple REST APIs for ecommerce purpose, available at http://localhost:8080.

## Requirements

- Docker + docker-compose
- an Internet connection (obviously).

## Installation

```
git clone https://github.com/michal-km/storeapi.git
cd storeapi
docker-compose up -d
```

## Use

Detailed documentation is provided in form of OpenAPI data. For browsing and testing you can use Swagger UI available at http://localhost:8080/docs/

### Catalog API

Please navigate to http://localhost:8080/docs/?urls.primaryName=catalog

CATALOG provides CRUD actions for managing a products:

- POST /catalog/api/v1/products for creating products,
- PATCH /catalog/api/v1/products/{id} for updating product data,
- GET /catalog/api/v1/products/{id} for fetching product data,
- DELETE /catalog/api/v1/products/{id} for deleting product.

Additionally, paginated list of product can be accessed by:

- GET /catalog/api/v1/products

### Cart API

Please navigate to http://localhost:8080/docs/?urls.primaryName=cart

CART provides actions for managing a shopping basket:

- GET /store/api/v1/carts/{id} returns a list of products added to the given cart along with total sum,
- PUT /store/api/v1/carts[/{id}] adds product to the cart (and creates the cart if no ID is given),
- DELETE /store/api/v1/carts/{id} removes all products from the cart and deletes the cart.

PUT method needs more explanation. This action takes an array of data in form of
```
{
	'id' => productId,
	'quantity' => numberOfPieces
}
'''
There are no separate actions for adding and removing products to/from the cart. For both operations PUT action should be used.
Positive quantity adds products to the cart, while negative quantity removes them. Basically, it tells the API how much pieces of given product be added or removed from the cart.
If for any product the resulting number of pieces is less than or equal to zero, the product is removed from the cart.

## Testing

In order to not spoil a production database, all tests are performed on a separate "store_test" database.

Tests are using a different configuration file, settings.test.php

To perform full functional test range, please run command:
```
docker exec -w /var/www gog_api vendor/bin/phpunit
```

## Additional notes

Similar project could be probably done under 30 minutes by using Symfony and API Platform. That was, however, not the point of this exercise. I decided to use Slim framework and build the API
engine from scratch, so there would be some code to review.

For performance reasons I used Nyholm HttpRequest implementation.

Designing the cart API was somewhat challenging, because standard CRUD operations cannot be used there. There is no point of editing or updating carts, and especially of adding carts for a single users.
Instead, I decided that a CartItem, an object containing information of a single product and its quantity, should be the subject of these operations. So, you can create cart item, update it and delete - that makes sense. That makes the cart a virtual entity, because it is just a sum of added products. No products = no cart. There is no table for carts, only for cart items.


## Copyright

This software is released into the public domain under the GPL-3 license.