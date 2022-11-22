<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Resource\Cart;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Resource\AbstractResourceHandler;
use App\Resource\Cart\CartToolsTrait;
use App\Validator\Validator;
use App\Entity\CartItem;

/**
 * PUT /store/api/v1/carts/{id}
 */
class PutCart extends AbstractResourceHandler implements RequestHandlerInterface
{
    use CartToolsTrait;

    /**
     * {@inheritDoc}
     *
     * @OA\Put(
     *     tags={"cart"},
     *     path="/store/api/v1/carts/{id}",
     *     operationId="putCart",
     *     summary = "Inserts (or removes) a product to/from a cart with given identifier.",
     *     description = "Positive quantity value adds given number of pieces to the cart identified by ID. Negative quantity value substract appropriate number of pieces from the cart. If the resulting number is less than or equal to 0, the product is removed from the cart.",
     *
     *     @OA\Parameter(name="id", in="path", required=false, description="The cart ID", example="b0145a23-14db-4219-b02a-53de833e470d", @OA\Schema(type="string")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Array with one or more products to be inserted or removed to/from the cart.",
     *         @OA\JsonContent(
     *            required={"items"},
     *            @OA\Property(
     *                property="items",
     *                type="array",
     *                @OA\Items(
     *                    @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                    @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                ),
     *            ),
     *         ),
     *      ),
     *
     *     @OA\Response(
     *      response="200",
     *      description="Cart was updated successfully",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  type="array",
     *                  title="items",
     *                  property="items",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                      @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  type="array",
     *                  title="meta",
     *                  property="meta",
     *                  @OA\Items(
     *                      @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *                      @OA\Property(property="cart.total", type="number", example="99.99"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *     ),
     *
     *     @OA\Response(
     *      response="201",
     *      description="Cart was created successfully",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  type="array",
     *                  title="items",
     *                  property="items",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                      @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  type="array",
     *                  title="meta",
     *                  property="meta",
     *                  @OA\Items(
     *                      @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *                      @OA\Property(property="cart.total", type="number", example="99.99"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *     ),
     *
     *     @OA\Response(
     *      response="304",
     *      description="Not changed",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(
     *              @OA\Property(
     *                  type="array",
     *                  title="items",
     *                  property="items",
     *                  @OA\Items(
     *                      @OA\Property(property="id", ref="#/components/schemas/CartItem/properties/ProductId"),
     *                      @OA\Property(property="quantity", ref="#/components/schemas/CartItem/properties/Quantity"),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  type="array",
     *                  title="meta",
     *                  property="meta",
     *                  @OA\Items(
     *                      @OA\Property(property="cart.id", ref="#/components/schemas/CartItem/properties/CartId"),
     *                      @OA\Property(property="cart.total", type="number", example="99.99"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Invalid input data",
     *     ),
     *
     *    @OA\Response(
     *      response="500",
     *      description="Server error",
     *     )
     * )
     */
    protected function processRequest(ServerRequestInterface $request): mixed
    {
        $this->authorize($request, "user");
        $created = false;

        $id = $request->getAttribute('id');
        $cartId = $this->getCartId($id);

        $params = $request->getParsedBody();
        if (!isset($params['items'])) {
            throw new \Exception('Not changed', 304);
        }

        // load existing cart items
        $cartItems = $this->getCartContents($cartId);
        if (count($cartItems) === 0) {
            $created = true;
        }

        // update with provided data
        $changed = $this->updateCartContents($cartItems, $params['items']);

        if (!$changed) {
            throw new \Exception('Not changed', 304);
        }

        // check before saving
        $this->validateProductCount($cartItems);

        // save cart
        $this->saveCartContents($cartItems, $cartId);

        $data = $this->getCartJSON($cartId);
        $data['code'] = $created ? 201 : 200;
        return $data;
    }

    /**
     * Validates cart ID provided in path.
     * If ID is invalid or empty, a new unique identifier is created.
     *
     * @param mixed $idParam Card ID to be validated.
     *
     * @return string Validated ID.
     */
    public function getCartId(mixed $idParam): string
    {
        $cartId = null;
        if (!empty($idParam)) {
            $cartId = trim(Validator::validateString('id', $idParam));
            $i = $this->getEntityManager()->getRepository(CartItem::class)->findBy(['CartId' => $cartId]);
            if (count($i) === null) {
                $cartId = null;
            }
        }
        if (null == $cartId) {
            $cartId = $this->createGUID();
        }
        return $cartId;
    }

    /**
     * Stores cart content in the database.
     * If a product has quantity of 0 or less, it is removed from the cart.
     *
     * @param array  $cartItems Array containing products added to the cart.
     * @param string $cartId    An unique cart identifier.
     */
    private function saveCartContents(array $cartItems, string $cartId): void
    {
        $em = $this->getEntityManager();
        foreach ($cartItems as $item) {
            if ($item['Quantity'] <= 0) {
                if (isset($item['Id'])) {
                    $cartItem = $em->getRepository(CartItem::class)->find($item['Id']);
                    $em->remove($cartItem);
                }
            } else {
                if (isset($item['Id'])) {
                    $cartItem = $em->getRepository(CartItem::class)->find($item['Id']);
                } else {
                    $cartItem = new CartItem($cartId, $item['ProductId'], $item['Quantity']);
                }
                $cartItem->setQuantity($item['Quantity']);
                $em->persist($cartItem);
            }
        }
        $em->flush();
    }

    /**
     * Adds or updated products in the cart.
     *
     * @param array $cartItems Array containing products added to the cart.
     * @param array $items     Array containing products to be added or removed.
     *
     * @return bool Returns true if the cart was updated, false if no change was made.
     */
    private function updateCartContents(array &$cartItems, array $items): bool
    {
        $changed = false;
        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['quantity'])) {
                throw new \Exception('Invalid data', 400);
            }
            $id = Validator::validateInteger('id', $item['id']);
            $quantity = Validator::validateInteger('quantity', $item['quantity']);
            if (0 !== $quantity) {
                if (isset($cartItems[$id])) {
                    // update existing product
                    $cartItems[$id]['Quantity'] += $quantity;
                    if ($cartItems[$id]['Quantity'] < 0) {
                        $cartItems[$id]['Quantity'] = 0;
                    }
                    $changed = true;
                } else {
                    // add product to cart
                    if ($this->isValidProduct($id)) {
                        $cartItems[$id] = [
                            'ProductId' => $id,
                            'Quantity' => $quantity,
                        ];
                        $changed = true;
                    }
                }
            }
        }
        return $changed;
    }

    /**
     * Returns products for a given cart identifier.
     *
     * @param string $cartId Cart identifier.
     *
     * @return array Array containing products added to the cart.
     */
    private function getCartContents(string $cartId): array
    {
        $cartItems = [];
        $cartRepository = $this->getEntityManager()->getRepository(CartItem::class);
        if ($cartId) {
            $c = $cartRepository->findBy(['CartId' => $cartId]);
            foreach ($c as $item) {
                $cartItems[$item->getProductId()] = [
                    'Id' => $item->getId(),
                    'ProductId' => $item->getProductId(),
                    'Quantity' => $item->getQuantity(),
                ];
            }
        }
        return $cartItems;
    }

    /**
     * Validation before storing in the database.
     * Checks if there are no more than 10 pieces of single products.
     * Checks if there are no more than 3 products in the cart.
     *
     * @param array $cartItems Array containing products added to the cart.
     */
    private function validateProductCount(array $cartItems): void
    {
        $productCount = 0;
        foreach ($cartItems as $item) {
            $quantity = $item['Quantity'];
            if ($quantity > 0) {
                $productCount++;
                if ($quantity > 10) {
                    throw new \Exception('Only 10 pieces of the same product is allowed', 304);
                }
            }
        }
        if ($productCount > 3) {
            throw new \Exception('Only 3 different products are allowed', 304);
        }
    }
}
