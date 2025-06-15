<?php

declare(strict_types=1);

namespace MartinPetricko\FilamentRestoreOrCreate\Concerns\CreateRecord;

use Filament\Actions\Action;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MartinPetricko\FilamentRestoreOrCreate\Actions\CheckDeletedAction;

/**
 * @phpstan-require-extends CreateRecord
 *
 * @mixin CreateRecord
 */
trait CheckDeleted
{
    public bool $restoreDeletedNotified = false;

    /**
     * @return string[]
     */
    protected function checkDeletedAttributes(): array
    {
        /** @var class-string<Resource> $resource */
        $resource = static::getResource();

        return [
            $resource::getRecordTitleAttribute(),
        ];
    }

    /**
     * @return ?string[]
     */
    protected function showDeletedAttributes(): ?array
    {
        return null;
    }

    /**
     * @param array<string, mixed> $data Attributes of a record that is being created.
     */
    protected function checkDeletedModel(array $data): ?Model
    {
        /** @var class-string<Resource> $resource */
        $resource = static::getResource();

        $checkDeletedData = array_filter(Arr::only($data, $this->checkDeletedAttributes()));

        return $resource::getEloquentQuery()
            ->where(fn (Builder $query) => $query->orWhere($checkDeletedData))
            ->onlyTrashed()
            ->latest()
            ->first();
    }

    protected function beforeCreate(): void
    {
        $deletedModel = $this->checkDeletedModel($this->data ?: []);
        if ($deletedModel === null || $this->restoreDeletedNotified === true) {
            return;
        }

        $this->restoreDeletedNotified = true;

        $this->mountAction('checkDeleted', ['deletedModel' => $deletedModel]);

        $this->halt();
    }

    protected function checkDeletedAction(): Action
    {
        return CheckDeletedAction::make()
            ->schema($this->getRestoreSchema())
            ->restoreRedirectUrl(fn (Model $record) => $this->getRestoreRedirectUrl($record))
            ->successNotification($this->getRestoredNotification());
    }

    protected function getRestoreSchema(): array
    {
        return [
            KeyValueEntry::make('deletedModel')
                ->hiddenLabel()
                ->state(function (Model $record) {
                    return collect($record->toArray())
                        ->only($this->showDeletedAttributes())
                        ->mapWithKeys(fn ($value, $key) => [Str::headline($key) => $value]);
                }),
        ];
    }

    protected function getRestoredNotification(): ?Notification
    {
        $title = $this->getRestoredNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function getRestoredNotificationTitle(): ?string
    {
        return __('filament-restore-or-create::actions.check_deleted.notifications.success.title');
    }

    protected function getRestoreRedirectUrl(Model $record): string
    {
        /** @var class-string<Resource> $resource */
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($record)) {
            return $resource::getUrl('view', ['record' => $record, ...$this->getRedirectUrlParameters()]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($record)) {
            return $resource::getUrl('edit', ['record' => $record, ...$this->getRedirectUrlParameters()]);
        }

        return $resource::getUrl();
    }
}
