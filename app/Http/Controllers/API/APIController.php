<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Validator as MyValidator;

class APIController extends Controller
{

    var $MESSAGE = "message";
    var $DATA = "data";
    var $SUCCESS = "success";

    public function getErrorResponse(MyValidator $validator)
    {
        return response()->json(
            [
                $this->MESSAGE => $validator->messages(),
                $this->DATA => null,
                $this->SUCCESS => false
            ],
            200);
    }
}
