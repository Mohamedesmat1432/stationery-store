<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait HandlesBulkActions
{
    /**
     * Handle single restore operation.
     */
    protected function performRestore($id, string $modelClass, string $serviceProperty, string $methodName): RedirectResponse
    {
        $model = $modelClass::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $model);

        $this->{$serviceProperty}->{$methodName}($model);

        return back()->with('success', 'Restored successfully.');
    }

    /**
     * Handle single force delete operation.
     */
    protected function performForceDelete($id, string $modelClass, string $serviceProperty, string $methodName): RedirectResponse
    {
        $model = $modelClass::onlyTrashed()->findOrFail($id);
        Gate::authorize('forceDelete', $model);

        $this->{$serviceProperty}->{$methodName}($model);

        return back()->with('success', 'Permanently deleted.');
    }

    /**
     * Handle bulk operations (delete, restore, forceDelete).
     */
    protected function performBulkAction(Request $request, string $modelClass, string $serviceProperty, string $entityPlural): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'action' => ['required', 'string', 'in:delete,restore,forceDelete'],
        ]);

        $config = [
            'delete' => [
                'method' => 'bulkDelete' . $entityPlural,
                'message' => 'Selected items deleted successfully.',
            ],
            'restore' => [
                'method' => 'bulkRestore' . $entityPlural,
                'message' => 'Selected items restored successfully.',
            ],
            'forceDelete' => [
                'method' => 'bulkForceDelete' . $entityPlural,
                'message' => 'Selected items permanently deleted.',
            ],
        ];

        $actionConfig = $config[$data['action']] ?? abort(400, 'Invalid action');

        Gate::authorize($data['action'], [$modelClass]);
        
        $this->{$serviceProperty}->{$actionConfig['method']}($data['ids']);

        return back()->with('success', $actionConfig['message']);
    }
}
