<?php

namespace App\Config;

use Symfony\Component\Yaml\Yaml;

final class SiteConfig
{
    private array $config;

    public function __construct(string $configPath, private readonly string $projectDir)
    {
        $configFile = $configPath . '/site.yaml';
        if (!file_exists($configFile)) {
            return;
        }

        $this->config = Yaml::parse(file_get_contents($configFile));

        $externalPathKeys = ['content_dir', 'build_dir'];

        foreach ($externalPathKeys as $key) {
            $this->handleExternalPath($key);
        }
    }

    public function __get(string $key): mixed
    {
        if (!array_key_exists($key, $this->config)) {
            return null;
        }

        return $this->config[$key];
    }

    private function handleExternalPath(string $key): void
    {
        if (!isset($this->config[$key])) {
            return;
        }

        if (str_starts_with($this->config[$key], '/')) {
            return;
        }

        $this->config[$key] = realpath($this->projectDir . '/' . $this->config[$key]);
    }
}