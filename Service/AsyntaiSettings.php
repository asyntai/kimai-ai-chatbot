<?php

/*
 * This file is part of the "AsyntaiChatbotBundle" for Kimai.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\AsyntaiChatbotBundle\Service;

use App\Configuration\ConfigurationService;
use App\Configuration\SystemConfiguration;
use App\Entity\Configuration;

/**
 * The plugin's settings, stored in Kimai's configuration table.
 */
final class AsyntaiSettings
{
    private const DEFAULT_ORIGIN = 'https://asyntai.com';

    public function __construct(
        private readonly SystemConfiguration $systemConfiguration,
        private readonly ConfigurationService $configurationService
    ) {
    }

    /**
     * Where the Asyntai server lives. A test install may point it elsewhere
     * with the ASYNTAI_ORIGIN environment variable.
     */
    public function getOrigin(): string
    {
        $origin = $_SERVER['ASYNTAI_ORIGIN'] ?? $_ENV['ASYNTAI_ORIGIN'] ?? getenv('ASYNTAI_ORIGIN');

        return rtrim(\is_string($origin) && $origin !== '' ? $origin : self::DEFAULT_ORIGIN, '/');
    }

    public function getSiteId(): string
    {
        $siteId = $this->read('asyntai.site_id');

        return self::isValidSiteId($siteId) ? $siteId : '';
    }

    public function getAccountEmail(): string
    {
        return $this->read('asyntai.account_email');
    }

    public function getScriptUrl(): string
    {
        return $this->cleanScriptUrl($this->read('asyntai.script_url'));
    }

    public function showToUsers(): bool
    {
        return $this->read('asyntai.show_users', '1') === '1';
    }

    public function showOnLogin(): bool
    {
        return $this->read('asyntai.show_login', '1') === '1';
    }

    public static function isValidSiteId(string $siteId): bool
    {
        return preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $siteId) === 1;
    }

    /**
     * Only an https asyntai.com address (or the configured origin) is accepted,
     * so a tampered setting cannot load a script from anywhere else.
     */
    public function cleanScriptUrl(string $url): string
    {
        $url = trim($url);
        if ($url !== '' && (preg_match('#^https://([a-z0-9-]+\.)*asyntai\.com/[^\s"\'<>]*$#i', $url) === 1
                || str_starts_with($url, $this->getOrigin() . '/'))) {
            return $url;
        }

        return $this->getOrigin() . '/static/js/chat-widget.js';
    }

    public function saveConnection(string $siteId, string $scriptUrl, string $accountEmail): void
    {
        $this->write('asyntai.site_id', $siteId);
        $this->write('asyntai.script_url', $scriptUrl);
        $this->write('asyntai.account_email', $accountEmail);
    }

    public function saveDisplay(bool $showUsers, bool $showLogin): void
    {
        $this->write('asyntai.show_users', $showUsers ? '1' : '0');
        $this->write('asyntai.show_login', $showLogin ? '1' : '0');
    }

    private function read(string $name, string $default = ''): string
    {
        $value = $this->systemConfiguration->find($name);

        return $value === null ? $default : (string) $value;
    }

    private function write(string $name, string $value): void
    {
        $entity = $this->configurationService->getConfiguration($name) ?? (new Configuration())->setName($name);
        $entity->setValue($value);
        $this->configurationService->saveConfiguration($entity);
    }
}
