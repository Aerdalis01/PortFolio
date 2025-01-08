<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

use GuzzleHttp\UriTemplate\UriTemplate;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;

final class Office365OAuthTokenProvider
{
    private const OAUTH_URL = 'https://login.microsoftonline.com/consumers/oauth2/v2.0/token';
    private const SCOPE = 'https://outlook.office365.com/.default';
    private const GRANT_TYPE = 'client_credentials';
    private const CACHE_KEY = 'email-token';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly Psr17Factory $psr17Factory,
        private readonly string $tenant,
        private readonly string $clientId,
        #[\SensitiveParameter]
        private readonly string $clientSecret,
        // set up some persistent cache for this, e.g. redis - to avoid having to re-authenticate with oauth2 all the time
        private readonly CacheInterface $cache,
    ) {
    }

    public function getToken(): string
    {
        return $this->cache->get(self::CACHE_KEY, [$this, 'fetchToken']);
    }

    public function fetchToken(CacheItem $cacheItem): string
    {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => self::SCOPE,
            'grant_type' => self::GRANT_TYPE,
        ];

        $body = $this->psr17Factory->createStream(http_build_query($data));
        $request = $this->psr17Factory->createRequest('POST', UriTemplate::expand(self::OAUTH_URL, [
            'tenant' => $this->tenant,
        ]))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($body)
        ;

        $response = $this->httpClient->sendRequest($request);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to fetch oauth token from microsoft: '.$response->getBody());
        }
        $auth = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $cacheItem->expiresAfter($auth['expires_in'] - 60); // substracting 60 seconds from the TTL as a safety margin to certainly not use an expiring token.

        return $auth['access_token'];
    }
}
