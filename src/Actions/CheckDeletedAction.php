<?php

declare(strict_types=1);

namespace MartinPetricko\FilamentRestoreOrCreate\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CheckDeletedAction extends Action
{
    protected string|Closure|null $restoreRedirectUrl = null;

    public static function getDefaultName(): ?string
    {
        return 'checkDeleted';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->record(fn (array $arguments) => $arguments['deletedModel'] ?? null)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(__('filament-restore-or-create::actions.check_deleted.modal_heading'))
            ->modalDescription(__('filament-restore-or-create::actions.check_deleted.modal_description'))
            ->modalSubmitActionLabel(__('filament-restore-or-create::actions.check_deleted.submit'))
            ->action(function (Model $record): void {
                if (!method_exists($record, 'restore')) {
                    throw new RuntimeException('Model does not have restore method.');
                }

                $record->restore();

                $this->success();

                $redirectUrl = $this->getRestoreRedirectUrl();
                if ($redirectUrl === null) {
                    return;
                }

                $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
            });
    }

    public function restoreRedirectUrl(string|Closure|null $restoreRedirectUrl): static
    {
        $this->restoreRedirectUrl = $restoreRedirectUrl;
        return $this;
    }

    public function getRestoreRedirectUrl(): ?string
    {
        /** @var string|null $restoreRedirectUrl */
        $restoreRedirectUrl = $this->evaluate($this->restoreRedirectUrl);
        return $restoreRedirectUrl;
    }
}
