# SelectableKeyValue Nova Field

A Laravel Nova custom field for key-value pairs where the key is a dropdown (select) with options set via `->keyOptions()`.

## Usage

```php
use LemonLabs\SelectableKeyValue\SelectableKeyValue;

SelectableKeyValue::make('Settings')
    ->keyOptions([
        'option1' => 'Option 1',
        'option2' => 'Option 2',
    ])
```

## Installation

1. Install the package in your Nova project:
   ```sh
   composer require lemonlabs/nova-selectable-key-value
   ```
2. Use the field in your Nova resource as shown above.
