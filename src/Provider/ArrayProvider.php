<?php declare(strict_types=1);

namespace Tolkam\Translator\Provider;

use Tolkam\Translator\TranslatorInterface;

class ArrayProvider implements LanguageProviderInterface
{
    /**
     * @var array
     */
    protected array $messages = [];
    
    /**
     * Whether to throw on missing values
     * @var bool
     */
    protected bool $strict;
    
    /**
     * Whether to fall back to previous nesting level segment
     * @var bool
     */
    protected bool $fallbackToPrevious;
    
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = array_replace([
            'strict' => false,
            'fallbackToPrevious' => true,
        ], $options);
        
        $this->strict = !!$options['strict'];
        $this->fallbackToPrevious = !!$options['fallbackToPrevious'];
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
        $sep = TranslatorInterface::SEP_LEVEL;
        $messages = $this->messages[$languageCode];
        $message = $messages[$messageCode] ?? null;
        
        if ($message === null) {
            if ($this->strict) {
                throw new LanguageProviderException(sprintf(
                    'No translation found at "%s" path',
                    $messageCode
                ));
            }
            
            // search for a fallback message
            if ($this->fallbackToPrevious) {
                $segments = explode($sep, $messageCode);
                while (array_pop($segments)) {
                    $message = implode($sep, $segments);
                    if (($message = $messages[$message] ?? null) && is_string($message)) {
                        break;
                    }
                }
            }
        }
        
        return $message;
    }
}
