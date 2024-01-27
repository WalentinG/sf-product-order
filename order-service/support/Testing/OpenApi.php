<?php

declare(strict_types=1);

namespace Support\Testing;

use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class OpenApi
{
    private static ?self $instance = null;
    private static ?PsrHttpFactory $psrFactory = null;

    public function __construct(private readonly ValidatorBuilder $v)
    {
    }

    public static function assertEndpoint(Request $sfRequest, Response $sfResponse): void
    {
        self::make()->v->getRequestValidator()->validate($request = self::psr()->createRequest($sfRequest));
        self::make()->v->getResponseValidator()->validate(
            opAddr: new OperationAddress($request->getUri()->getPath(), strtolower($sfRequest->getMethod())),
            response: self::psr()->createResponse($sfResponse),
        );
    }

    /** @param array<string, mixed> $message */
    public static function assertEvent(array $message, string $type): void
    {
        $openapi = self::make()->v->getRequestValidator()->getSchema();
        $schema = $openapi->components?->schemas[$type];
        if ($schema instanceof Schema) {
            (new SchemaValidator())->validate($message, $schema);
        }
    }

    private static function make(): self
    {
        return self::$instance ?? self::$instance = new self(
            (new ValidatorBuilder())->fromYamlFile(dirname(__DIR__) . '/../openapi.yml')
        );
    }

    private static function psr(): PsrHttpFactory
    {
        return self::$psrFactory ?? self::$psrFactory = new PsrHttpFactory(
            serverRequestFactory: $psr17Factory = new Psr17Factory(),
            streamFactory: $psr17Factory,
            uploadedFileFactory: $psr17Factory,
            responseFactory: $psr17Factory
        );
    }
}
