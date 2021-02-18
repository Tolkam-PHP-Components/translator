<?php declare(strict_types=1);

namespace Tolkam\Translator\Provider;

use Tolkam\Translator\TranslatorInterface;

class NestedArrayProvider implements LanguageProviderInterface
{
    /**
     * Root key when final path resolves to array instead of value
     */
    protected const ROOT_KEY = '';
    
    /**
     * @var array
     */
    protected array $messages = [];
    
    /**
     * @var array
     */
    protected array $options = [
        'strict' => false,
    ];
    
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_replace($this->options, $options);
    }
    
    /**
     * @param string $languageCode
     * @param array  $messages
     *
     * @return self
     */
    public function setMessages(string $languageCode, array $messages): self
    {
        $this->messages[$languageCode] = $messages;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function hasLanguage(string $languageCode): bool
    {
        return isset($this->messages[$languageCode]);
    }
    
    /**
     * @inheritDoc
     */
    public function getMessage(string $languageCode, string $messageCode): ?string
    {
        $messages = $this->messages[$languageCode];
        $message = $messages;
        
        if (mb_strpos($messageCode, TranslatorInterface::SEP_LEVEL) === false) {
            $message = $message[$messageCode] ?? null;
        }
        
        foreach (explode(TranslatorInterface::SEP_LEVEL, $messageCode) as $segment) {
            if (is_array($message) && isset($message[$segment])) {
                $message = $message[$segment];
            }
            else {
                $message = null;
            }
        }
        
        // last message was array but segment was not found - look for array default value
        if (is_array($message) && isset($message[self::ROOT_KEY])) {
            $message = $message[self::ROOT_KEY];
        }
        
        if ($this->options['strict'] && $message === null) {
            throw new LanguageProviderException(sprintf(
                'No translation found at "%s" path for "%s" language',
                $messageCode,
                $languageCode
            ));
        }
        
        return $message !== null ? (string) $message : $message;
    }
}
