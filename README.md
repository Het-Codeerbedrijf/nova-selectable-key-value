# SelectableKeyValue Nova Field

A Laravel Nova custom field for key-value pairs where the key is a dropdown (select) with options set via `->options()`.

## Usage

```php
use NowakAdmin\SelectableKeyValue\SelectableKeyValue;

SelectableKeyValue::make('Settings')
    ->options([
        'option1' => 'Option 1',
        'option2' => 'Option 2',
    ])
```

## Installation

1. Install the package in your Nova project:
   ```sh
   composer require nowakadmin/selectable-key-value
   ```
2. Build assets:
   ```sh
   cd nova-components/SelectableKeyValue
   npm install && npm run dev
   ```
3. Register the field in your Nova resource as shown above.
