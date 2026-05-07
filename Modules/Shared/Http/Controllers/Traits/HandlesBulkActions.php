<?php

namespace Modules\Shared\Http\Controllers\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Modules\Shared\Enums\BulkActionType;

trait HandlesBulkActions
{
    /**
     * Handle single restore operation.
     */
    protected function performRestore(string $id, string $modelClass, string $serviceProperty, string $methodName): RedirectResponse
    {
        $model = $modelClass::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $model);

        $this->{$serviceProperty}->{$methodName}($model);

        return back()->with('success', __('Restored successfully.'));
    }

    /**
     * Handle single force delete operation.
     */
    protected function performForceDelete(string $id, string $modelClass, string $serviceProperty, string $methodName): RedirectResponse
    {
        $model = $modelClass::onlyTrashed()->findOrFail($id);
        Gate::authorize('forceDelete', $model);

        $this->{$serviceProperty}->{$methodName}($model);

        return back()->with('success', __('Permanently deleted.'));
    }

    /**
     * Handle bulk operations (delete, restore, forceDelete).
     */
    protected function performBulkAction(Request $request, string $modelClass, string $serviceProperty): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'action' => ['required', new Enum(BulkActionType::class)],
        ]);

        $action = BulkActionType::from($data['action']);

        $config = [
            BulkActionType::DELETE->value => [
                'method' => 'bulkDelete',
                'message' => __('Selected items deleted successfully.'),
            ],
            BulkActionType::RESTORE->value => [
                'method' => 'bulkRestore',
                'message' => __('Selected items restored successfully.'),
            ],
            BulkActionType::FORCE_DELETE->value => [
                'method' => 'bulkForceDelete',
                'message' => __('Selected items permanently deleted.'),
            ],
            BulkActionType::ACTIVATE->value => [
                'method' => 'bulkActivate',
                'message' => __('Selected items activated successfully.'),
            ],
            BulkActionType::DEACTIVATE->value => [
                'method' => 'bulkDeactivate',
                'message' => __('Selected items deactivated successfully.'),
            ],
        ];

        $actionConfig = $config[$action->value];

        Gate::authorize($action->value, $modelClass);

        $this->{$serviceProperty}->{$actionConfig['method']}($data['ids']);

        return back()->with('success', $actionConfig['message']);
    }
}
