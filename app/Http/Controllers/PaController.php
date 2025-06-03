<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paatt;
use App\Models\Paitem;
use Illuminate\Http\Request;
use App\Models\Pa;

class PaController extends Controller
{
    public function uploadPa(Request $request, $company)
    {
        $request->validate([
            'payload' => 'required'
        ]);

        if ($request->remark) {
            $remark = $request->payload['remark'];
        } else {
            $remark = $request->payload['desc'];
        }

        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        $rev = 0;
        $count = Pa::distinct('pa_id')->where('pa_id', 'LIKE', '%/' . $company . '/%')->count('pa_id');
        $number = $count + 1;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        $month = date('n');
        $year = date('y');
        $pa_id = 'PA/' . $number . '/' . $company . '/' . $romanMonths[$month] . '/' . $year;
        $pa = Pa::create([
            'id_user' => $request->payload['id_user'],
            'ref_inv' => $request->payload['ref_inv'],
            'pa_id' => $pa_id,
            'desc' => $request->payload['desc'],
            'category' => $request->payload['category'],
            'project' => $request->payload['project'],
            'operation_device' => $request->payload['operation_device'],
            'remark' => $remark,
            'total' => $request->payload['total'],
            'rev' => $rev
        ]);

        if ($request->payload['paitem']) {
            foreach ($request->payload['paitem'] as $item) {
                $allPaItems[] = Paitem::create([
                    'id_pa' => $pa['id'],
                    'item' => $request->payload['item'],
                    'propose_price' => $request->payload['propose_price'],
                    'approve_price' => $request->payload['approve_price']
                ]);
            }
        }
        if ($request->payload['paatt']) {
            $cleanPaId = str_replace([' ', '\\', '/'], '_', $pa_id);
            $folderPath = $company . '/' . $cleanPaId;
            foreach ($request->file('payload.paatt') as $item) {
                $timestamp = time();
                $originalName = pathinfo($item->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $item->getClientOriginalExtension();

                $fileName = 'pa_' . $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
                $path = $item->storeAs($folderPath, $fileName, 'public');
                $paatts[] = Paatt::create([
                    'name' => $originalName,
                    'id_pa' => $pa['id'],
                    'path' => $path
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $pa->pa_id . ' upload success',
            'pa' => $pa,
            'paitem' => $allPaItems,
            'paatt' => $paatts
        ]);
    }
}
