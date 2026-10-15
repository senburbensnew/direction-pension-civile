<?php

namespace App\Http\Controllers\Formalites;

use App\Http\Controllers\Admin\PensionMandatManagementController;
use Illuminate\View\View;

class PensionMandatFormalitesController extends PensionMandatManagementController
{
    public function index()
    {
        $response = parent::index();

        if ($response instanceof View) {
            return $response->with([
                'approveRoute' => 'formalites.pension-mandats.approve',
                'refuseRoute' => 'formalites.pension-mandats.refuse',
                'revokeRoute' => 'formalites.pension-mandats.revoke',
                'pieceRoute' => 'formalites.pension-mandats.piece',
            ]);
        }

        return $response;
    }
}
