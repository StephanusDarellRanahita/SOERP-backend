<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function uploadClient(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'pic_1' => 'required|max:100',
            'rule_1' => 'required|max:50',
            'phone_1' => 'required|max:50'
        ]);

        $lastClient = Client::latest('id')->first();

        if ($lastClient && preg_match('/^(\d+)([A-Z]*)$/', $lastClient->customer_id, $matches)) {
            $lastNumber = (int) $matches[1];
            $lastLetter = $matches[2] ?? 'A';

            if ($lastNumber < 199999) {
                // ✅ Corrected: Increment the number first!
                $newNumber = $lastNumber + 1;
                $newLetter = $lastLetter;
            } else {
                // If number reaches 99999, reset it and increment the letter
                $newNumber = 100001;
                $newLetter = chr(ord($lastLetter) + 1);
            }

            $customer_id = $newNumber . $newLetter;
        } else {
            $customer_id = '100001A';
        }

        $client = Client::create([
            'name' => $request->name,
            'pic_1' => $request->pic_1,
            'rule_1' => $request->rule_1,
            'phone_1' => $request->phone_1,
            'customer_id' => $customer_id
        ]);

        return response()->json([
            'message' => 'Client uploaded successfully',
            'customer' => $client
        ]);
    }

    public function getClient() {
        $client = Client::all();

        return response()->json([
            'message' => 'Retrieve client successful',
            'client' => $client
        ]);
    }
}
