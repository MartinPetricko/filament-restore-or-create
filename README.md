# Filament Restore or Create

[![Latest Version on Packagist](https://img.shields.io/packagist/v/martinpetricko/filament-restore-or-create.svg?style=flat-square)](https://packagist.org/packages/martinpetricko/filament-restore-or-create)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/martinpetricko/filament-restore-or-create/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/martinpetricko/filament-restore-or-create/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/martinpetricko/filament-restore-or-create/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/MartinPetricko/filament-restore-or-create/actions?query=workflow%3A%22Fix+PHP+Code+Styling%22branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/martinpetricko/filament-restore-or-create.svg?style=flat-square)](https://packagist.org/packages/martinpetricko/filament-restore-or-create)

Restore or Create is a FilamentPHP plugin that helps prevent duplicate records by detecting and restoring soft-deleted models when similar data is submitted via a create form.

## Features
- Detects similar soft-deleted records before creating a new one.
- Displays a modal with details and an option to restore the deleted record.
- Fully customizable detection, display, and behavior logic.

## Installation

Install via Composer:

```bash
composer require martinpetricko/filament-restore-or-create
```

Optionally, publish the translation files:

```bash
php artisan vendor:publish --tag="filament-restore-or-create-translations"
```

## Usage

Add `CheckDeleted` trait to your resource's `CreateRecord` page:

```php
use MartinPetricko\FilamentRestoreOrCreate\Concerns\CreateRecord\CheckDeleted;

class CreateUser extends CreateRecord
{
    use CheckDeleted;

    protected static string $resource = UserResource::class;
}
```

## Customization

### Attributes to check

Define which fields should be used to detect similar deleted records:

```php
protected function checkDeletedAttributes(): array
{
    return ['name', 'email', 'phone'];
}
```

### Custom Query Logic

Override the default query to define your own matching logic:

```php
protected function checkDeletedModel(array $data): ?Model
{
    return static::getResource()::getEloquentQuery()
        ->whereLike('name', '%' . $data['name'] . '%')
        ->onlyTrashed()
        ->latest()
        ->first();
}
```

### Modal Display Fields

Choose which attributes to show in the confirmation modal:

```php
protected function showDeletedAttributes(): ?array
{
    return ['name', 'email', 'phone', 'address', 'deleted_at'];
}
```

### Restore Notification

Customize the notification shown after a record is restored:

```php
protected function getRestoredNotification(): ?Notification
{
    return Notification::make()
        ->success()
        ->title('Restored');
}
```

### Redirect After Restore

Control where the user is redirected after restoring:

```php
protected function getRestoreRedirectUrl(Model $record): string
{
    /** @var class-string<Resource> $resource */
    $resource = static::getResource();

    if ($resource::hasPage('edit') && $resource::canEdit($record)) {
        return $resource::getUrl('edit', ['record' => $record]);
    }

    return $resource::getUrl();
}
```

## Advanced Integration

If you override `beforeCreate` or `getFormActions`, ensure the restore behavior is still called:

```php
class CreateUser extends CreateRecord
{
    use CheckDeleted {
        beforeCreate as checkDeletedBeforeCreate;
    }
    
    protected function getFormActions(): array
    {
        return [
            // Custom form actions
            
            $this->getCheckDeletedFormAction(),
        ];
    }

    protected function beforeCreate(): void
    {
        $this->checkDeletedBeforeCreate();
        
        // Your custom logic
    }
}
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Martin Petricko](https://github.com/MartinPetricko)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
