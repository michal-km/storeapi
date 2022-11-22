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
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7;
use App\Validator\Validator;

/**
 * Abstract handler for API actions.
 */
abstract class AbstractResourceHandler
{
    private ContainerInterface $serviceContainer;

    /**
     * {@inheritDoc}
     */
    public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return ContainerInterface An instance of ServiceContainer.
     */
    protected function getServiceContainer(): ContainerInterface
    {
        return $this->serviceContainer;
    }

    /**
     * @return EntityManager An instance of EntityManager.
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->serviceContainer->get(EntityManager::class);
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
     * Returns a path to the API server.
     *
     * @return string Base API URL.
     */
    protected function getServer(): string
    {
        return 'http://localhost:8080/';
    }

    /**
     * Gets and validates parameter from POST or GET array.
     */
    protected function requireParameter($params, string $name, string $type, bool $required = true): mixed
    {
        if (!isset($params[$name])) {
            if ($required) {
                throw new \Exception('Invalid input data (' . $name . ')', 400);
            } else {
                return null;
            }
        }
        $value = $params[$name];

        switch ($type) {
            case 'string':
                $value = Validator::validateString($name, $value);
                break;
            case 'integer':
                $value = Validator::validateString($name, $value);
                break;
            case 'price':
                $value = Validator::validatePrice($name, $value);
                break;
        }

        return $value;
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
