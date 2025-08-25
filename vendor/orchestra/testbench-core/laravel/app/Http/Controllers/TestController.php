<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class TestController extends Controller
{
    /**
     * @api {get} /api/test Get test data
     * @apiName GetTest
     * @apiGroup Test
     * @apiDescription Get test data from the API
     * 
     * @apiParam {String} [page] Page number
     * 
     * @apiSuccess {Object[]} data Array of test data
     * 
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": []
     *     }
     */
    public function index()
    {
        return response()->json(['data' => []]);
    }
}