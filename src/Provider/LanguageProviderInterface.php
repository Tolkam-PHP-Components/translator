<?php declare(strict_types=1);

namespace Tolkam\Translator\Provider;

interface LanguageProviderInterface
{
    /**
     * Checks if language is available
     *
     * @param string $languageCode
     *
     * @return bool
     */
    public function hasLanguage(string $languageCode): bool;
    
    /**
     * Gets message
     *
     * @param string $languageCode
     * @param string $messageCode
     *
     * @return string|null
     */
    public function getMessage(string $languageCode, string $messageCode): ?string;
}
