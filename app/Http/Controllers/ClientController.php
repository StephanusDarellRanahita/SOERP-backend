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

    public function uploadDetailClient(Request $request)
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
            'customer_id' => $customer_id,
            'address' => $request->address,
            'business_sector' => $request->business_sector,
            'email_1' => $request->email_1,
            'email_2' => $request->email_2,
            'name' => $request->name,
            'npwp' => $request->npwp,
            'office_phone' => $request->office_phone,
            'phone_1' => $request->phone_1,
            'phone_2' => $request->phone_2,
            'pic_1' => $request->pic_1,
            'pic_2' => $request->pic_2,
            'rule_1' => $request->rule_1,
            'rule_2' => $request->rule_2,
            'website' => $request->website
        ]);

        return response()->json([
            'success' => true,
            'message' => $client->name . " successfully added",
            'client' => $client
        ]);
    }

    public function updateClient(Request $request, $id)
    {
        $client = Client::where('id', $id)->first();

        $client->update([
            'address' => $request->address,
            'business_sector' => $request->business_sector,
            'email_1' => $request->email_1,
            'email_2' => $request->email_2,
            'name' => $request->name,
            'npwp' => $request->npwp,
            'office_phone' => $request->office_phone,
            'phone_1' => $request->phone_1,
            'phone_2' => $request->phone_2,
            'pic_1' => $request->pic_1,
            'pic_2' => $request->pic_2,
            'rule_1' => $request->rule_1,
            'rule_2' => $request->rule_2,
            'website' => $request->website
        ]);

        return response()->json([
            'success' => true,
            'message' => $client->name . " data successfully updated",
            'client' => $client
        ]);
    }

    public function deleteClient($id)
    {
        $client = Client::where('id', $id)->first();

        if (!$client) {
            return response()->json([
                'message' => "Client not found"
            ]);
        }

        $client->delete();
        return response()->json([
            'success' => true,
            'message' => $client->name . " deleted successfully"
        ]);
    }

    public function getClient()
    {
        $client = Client::all();

        return response()->json([
            'message' => 'Retrieve client success',
            'client' => $client
        ]);
    }

    public function countClient()
    {
        $count = Client::count();

        return response()->json([
            'message' => "Counting clients success",
            'client' => $count
        ]);
    }
}
