# tolkam/translator

Translates message codes into human-readable strings.

## Documentation

The code is rather self-explanatory and API is intended to be as simple as possible. Please, read the sources/Docblock if you have any questions. See [Usage](#usage) for quick start.

## Usage

````php
use Tolkam\Translator\Provider\ArrayProvider;
use Tolkam\Translator\Provider\NestedArrayProvider;
use Tolkam\Translator\Translator;

$translator = new Translator;

// translations from one-dimensional array
$translator->addProvider(
    (new ArrayProvider)->setMessages('en', [
        'my.code' => 'Messages: {count}',
        'my.code.plural' => 'You have {count|%d message,%d messages,no messages}',
    ])
);

// or from multi-dimensional array
$translator->addProvider(
    (new NestedArrayProvider)->setMessages('ru', [
        'my' => [
            'code' => [
                '' => 'Сообщений: {count}',
                'plural' => 'У вас {count|%d сообщение,%d сообщения,%d сообщений,нет сообщений}',
            ],
        ],
    ])
);

foreach (['en', 'ru'] as $language) {
    $translator->useLanguage($language);
    
    echo $translator->get('my.code', ['count' => 0]) . PHP_EOL;
    
    foreach ([0, 1, 2, 5, 21] as $count) {
        echo $translator->get('my.code.plural', ['count' => $count]) . PHP_EOL;
    }
    
    echo PHP_EOL;
}
````

Output:

````
Messages: 0
You have no messages
You have 1 message
You have 2 messages
You have 5 messages
You have 21 messages

Сообщений: 0
У вас нет сообщений
У вас 1 сообщение
У вас 2 сообщения
У вас 5 сообщений
У вас 21 сообщение
````

## License

Proprietary / Unlicensed 🤷
