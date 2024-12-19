<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $letter = $request->get('letter', 'A');

        $clients = Client::where('name', 'LIKE', $letter . '%')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json(['clients' => $clients]);
    }

}
