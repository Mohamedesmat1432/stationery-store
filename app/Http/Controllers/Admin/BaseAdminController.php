<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Traits\HandlesBulkActions;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class BaseAdminController extends Controller
{
    use HandlesBulkActions;

    /**
     * The model class for this controller.
     */
    protected string $modelClass;

    /**
     * The service property name.
     */
    protected string $serviceProperty;

    /**
     * Base name for service methods (e.g., 'User' for 'restoreUser').
     */
    protected string $serviceMethodBase;

    public function restore($id): RedirectResponse
    {
        return $this->performRestore(
            $id,
            $this->modelClass,
            $this->serviceProperty,
            "restore{$this->serviceMethodBase}"
        );
    }

    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete(
            $id,
            $this->modelClass,
            $this->serviceProperty,
            "forceDelete{$this->serviceMethodBase}"
        );
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, $this->modelClass, $this->serviceProperty);
    }
}
