<?php declare(strict_types=1);

namespace Tolkam\Translator;

use Tolkam\Translator\Provider\LanguageProviderInterface;
use Tolkam\Utils\I18n;

/**
 * Usage example:
 *
 * <code>
 *      $t = new Translator;
 *      $t->addProvider(
 *          (new ArrayProvider)->setMessages('ru', [
 *              'my.code' => 'Сообщений: {count}',
 *              'my.code.plural' => 'У вас {count|%d сообщение,%d сообщения,%d сообщений,нет сообщений}',
 *          ])
 *      );
 *      $t->setLanguage('ru');
 *      $message = $t->get('my.code.plural', ['count' => 0]);
 * </code>
 */
class Translator implements TranslatorInterface
{
    /**
     * @var LanguageProviderInterface[]
     */
    protected array $providers = [];
    
    /**
     * @var string|null
     */
    protected ?string $language = null;
    
    /**
     * @var bool
     */
    protected bool $fallbackToCode;
    
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = array_replace([
            'fallbackToCode' => false,
        ], $options);
        
        $this->fallbackToCode = !!$options['fallbackToCode'];
    }
    
    /**
     * @param LanguageProviderInterface $provider
     *
     * @return self
     */
    public function addProvider(LanguageProviderInterface $provider): self
    {
        $this->providers[] = $provider;
        
        return $this;
    }
    
    /**
     * Sets the language code
     *
     * @param string $lang
     *
     * @return self
     */
    public function setLanguage(string $lang): self
    {
        if (mb_strlen($lang) !== 2) {
            throw new TranslatorException('Language code must be 2 characters string');
        }
        
        $this->language = mb_strtolower($lang);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function get(string $code, array $args = []): ?string
    {
        $message = $this->fallbackToCode ? $code : null;
        
        if ($lang = $this->language) {
            foreach ($this->getProviders($lang) as $provider) {
                $message = $provider->getMessage($lang, $code) ?? $message;
                if ($message !== null) {
                    if (preg_match_all('~{.+?}~', $message, $matches)) {
                        $message = $this->applyPlural($message, $matches[0], $args);
                    }
                    break;
                }
            }
        }
        
        return $message;
    }
    
    /**
     * Gets capable message provider
     *
     * @param string $lang
     *
     * @return LanguageProviderInterface[]
     * @throws TranslatorException
     */
    private function getProviders(string $lang): array
    {
        $capable = [];
        foreach ($this->providers as $provider) {
            if ($provider->hasLanguage($lang)) {
                $capable[] = $provider;
            }
        }
        
        if (empty($capable)) {
            throw new TranslatorException(sprintf(
                'No capable provider found for "%s" language',
                $lang
            ));
        }
        
        return $capable;
    }
    
    /**
     * @param string $message
     * @param array  $placeholders
     * @param array  $args
     *
     * @return string
     */
    private function applyPlural(string $message, array $placeholders, array $args): string
    {
        $map = [];
        foreach ($placeholders as $placeholder) {
            $stripped = substr(substr($placeholder, 1), 0, -1);
            [$arg, $forms] = explode(self::SEP_FORMS, $stripped) + [null, null];
            
            if ($forms) {
                $count = $args[$arg] ?? '';
                $intCount = (int) $count;
                $forms = explode(self::SEP_FORM, $forms);
                $pluralIndex = I18n::pluralIndex($this->language, $intCount);
                // last form is for zero
                if ($intCount === 0) {
                    $zeroFormIndex = $pluralIndex + 1;
                    if (isset($forms[$zeroFormIndex])) {
                        $pluralIndex = $zeroFormIndex;
                    }
                }
                $replacement = sprintf($forms[$pluralIndex] ?? '', $count);
            }
            else {
                $replacement = $args[$arg];
            }
            
            $map[$placeholder] = $replacement;
        }
        
        return strtr($message, $map);
    }
}
