<?php

namespace App\Http\Controllers;

use App\Support\Admin\AdminModelDeletion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function adminDelete(Model $model, ?Request $request = null): string
    {
        return AdminModelDeletion::apply($model, $request ?? request());
    }
}
