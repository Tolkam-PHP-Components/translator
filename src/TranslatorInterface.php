<?php declare(strict_types=1);

namespace Tolkam\Translator;

interface TranslatorInterface
{
    public const SEP_LEVEL = '.';
    public const SEP_FORMS = '|';
    public const SEP_FORM  = ',';
    
    /**
     * Sets the language code to use
     *
     * @param string $lang
     *
     * @return self
     */
    public function useLanguage(string $lang): self;
    
    /**
     * Gets message by its code
     *
     * @param string $code
     * @param array  $args
     *
     * @return string|null
     * @throws TranslatorException
     */
    public function get(string $code, array $args = []): ?string;
}
