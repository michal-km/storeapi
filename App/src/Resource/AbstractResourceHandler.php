<?php

declare(strict_types=1);

/*
 * This file is part of the recruitment exercise.
 *
 * @author Michal Kazmierczak <michal.kazmierczak@oldwestenterprises.pl>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Resource;

use Doctrine\ORM\EntityManager;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7;

/**
 * Abstract handler for API actions.
 */
abstract class AbstractResourceHandler
{
    private EntityManager $entityManager;

    /**
     * {@inheritDoc}
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager An instance of EntityManager.
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Assures that provided value is a valid integer, throws exception otherwise.
     * Negative values are allowed.
     *
     * @param string $name  Argument name for reference.
     * @param mixed  $value Value that needs validation.
     *
     * @return int Validated integer.
     */
    protected function validateInteger(string $name, mixed $value): int
    {
        if (!is_numeric($value) || empty($value) || $value != (int) $value) {
            throw new \Exception("Invalid input parameter \"" . $name . "\"", 400);
        }
        return (int) $value;
    }

    /**
     * Assures that provided value is a valid price, throws exception otherwise.
     * Fractional part can be separated by dot or comma. Negative values are prohibited.
     *
     * @param string $name  Argument name for reference.
     * @param mixed  $value Value that needs validation.
     *
     * @return int Validated price.
     */
    protected function validatePrice(string $name, mixed $value): int
    {
        if (!is_numeric($value) || empty($value)) {
            throw new \Exception("Invalid input parameter \"" . $name . "\"", 400);
        }
        $value = str_replace(',', '.', (string) $value);
        $value = round($value * 100);
        if ($value < 0) {
            throw new \Exception("Price cannot be a negative number", 400);
        }
        return (int) $value;
    }

    /**
     * Assures that provided value is a valid string, throws exception otherwise.
     * Length should be in 1-255 range.
     *
     * @param string $name  Argument name for reference.
     * @param mixed  $value Value that needs validation.
     *
     * @return string Validated string.
     */
    protected function validateString(string $name, mixed $value): string
    {
        $value = (string) $value;
        if (empty($value)) {
            throw new \Exception("Invalid input parameter \"" . $name . "\"", 400);
        }
        if (strlen($value) > 255) {
            throw new \Exception("String too long", 400);
        }
        return (string) $value;
    }

    /**
     * Any security logic should be placed here.
     * Right now it is a dummy authorization check, always returns true.
     *
     * @param ServerRequestInterface $request Request object for checking header values.
     * @param string                 $role    User role to check access against.
     */
    protected function authorize(ServerRequestInterface $request, string $role): void
    {
        $authorized = true;
        // place security logic here
        if (false === $authorized) {
            throw new \Exception('Unauthorized', 401);
        }
    }

    /**
     * Common logic for handling API requests.
     *
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $this->processRequest($request);
            $returnCode = 200;
            if (is_array($data) && isset($data['code'])) {
                $returnCode = $data['code'];
                unset($data['code']);
            }
        } catch (\Exception $e) {
            $returnCode = 500;
            $returnCode = $e->getCode();
            if (0 === $returnCode) {
                $returnCode = 400;
            }
            $data = [
                'message' => $e->getMessage(),
            ];
        }
        $payload = Psr7\Stream::create(json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL);

        $response = new Psr7\Response($returnCode, ['Content-Type' => 'application/json'], $payload);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');

        return $response;
    }

    /**
     * API action logic should be placed here.
     *
     * @param ServerRequestInterface $request Received API request
     *
     * @return mixed Data which will be encoded to JSON and returned in response.
     */
    abstract protected function processRequest(ServerRequestInterface $request): mixed;
}
