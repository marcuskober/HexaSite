<?php

namespace App\Torchlight;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TorchlightApi
{
    public function __construct(
        private string $apiKey,
        private CacheInterface $cache,
        private HttpClientInterface $httpClient,
    )
    {
    }

    public function highlight(string $code, string $language): string
    {
        $cacheKey = md5($code . $language);

        return $this->cache->get($cacheKey, function() use ($code, $language) {
            // API-Aufruf an Torchlight
            $response = $this->httpClient->request('POST', 'https://api.torchlight.dev/highlight', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
                'json' => [
                    'blocks' => [
                        [
                            'code' => $code,
                            'language' => $language,
                            'theme' => 'dracula',
                        ],
                    ],
                ]
            ]);

            $data = $response->toArray();

            return $data['blocks'][0]['wrapped']; // Nehme den hervorgehobenen Code aus der Antwort
        });
    }
}