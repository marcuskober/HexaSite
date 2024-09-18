<?php

namespace App\Config;

use Symfony\Component\Yaml\Yaml;

final class SiteConfig
{
    private array $config;

    public function __construct(string $configPath)
    {
        $configFile = $configPath . '/site.yaml';
        if (!file_exists($configFile)) {
            return;
        }

        $this->config = Yaml::parse(file_get_contents($configFile));
    }

    public function __get(string $key): mixed
    {
        if (!array_key_exists($key, $this->config)) {
            return null;
        }

        return $this->config[$key];
    }
}