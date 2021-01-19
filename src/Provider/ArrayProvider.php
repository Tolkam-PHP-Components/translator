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
     * Whether to fallback to previous nesting level segment
     * @var bool
     */
    protected bool $fallbackToPrevious;
    
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = array_replace([
            'fallbackToPrevious' => true,
        ], $options);
        
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
        // search for a fallback message
        if ($message == null && $this->fallbackToPrevious) {
            $segments = explode($sep, $messageCode);
            while (array_pop($segments)) {
                $message = implode($sep, $segments);
                if (($message = $messages[$message] ?? null) && is_string($message)) {
                    break;
                }
            }
        }
        
        return $message;
    }
}
