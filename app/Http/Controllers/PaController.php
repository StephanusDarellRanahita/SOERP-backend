<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paatt;
use App\Models\Paitem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Pa;

class PaController extends Controller
{
    public function uploadPa(Request $request, $company)
    {
        $request->validate([
            'payload' => 'required'
        ]);

        $payload = json_decode($request->input('payload'), true);

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
        if ($payload['pa_id']) {
            $prev_pa = Pa::where('pa_id', $payload['pa_id'])
                ->where('status', '!=', 'Revision')->first();
            $pa_id = $prev_pa->pa_id;
            $rev = $prev_pa->rev + 1;
            $prev_pa->update([
                'status' => 'Revision'
            ]);
        } else {
            $rev = 0;
            $count = Pa::distinct('pa_id')->where('pa_id', 'LIKE', '%/' . $company . '/%')->count('pa_id');
            $number = $count + 1;
            $number = str_pad($number, 3, '0', STR_PAD_LEFT);
            $month = date('n');
            $year = date('y');
            $pa_id = 'PA/' . $number . '/' . $company . '/' . $romanMonths[$month] . '/' . $year;
        }
        $pa = Pa::create([
            'applicant' => $payload['applicant'],
            'ref_inv' => $payload['ref_inv'] == 0 ? null : $payload['ref_inv'],
            'pa_id' => $pa_id,
            'bank' => $payload['bank'],
            'bank_account' => $payload['bank_account'],
            'desc' => $payload['desc'],
            'category' => $payload['category'],
            'project' => $payload['project'],
            'operation_device' => $payload['operation_device'],
            'remark' => $payload['remark'],
            'total' => $payload['total'],
            'currency' => $payload['currency'],
            'rev' => $rev,
            'status' => 'Pending'
        ]);

        if ($payload['paitems']) {
            foreach ($payload['paitems'] as $item) {
                $allPaItems[] = Paitem::create([
                    'id_pa' => $pa['id'],
                    'item' => $item['desc'],
                    'propose_price' => $item['total'],
                    'approve_price' => $item['approve_price']
                ]);
            }
        }
        if ($request->hasFile('paatts')) {
            $cleanPaId = str_replace([' ', '\\', '/'], '_', $pa_id);
            $folderPath = $company . '/' . $cleanPaId;
            foreach ($request->file('paatts') as $item) {
                $timestamp = time();
                $originalName = pathinfo($item->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $item->getClientOriginalExtension();
                $size = $item->getSize();
                $type = $item->getMimeType();

                $fileName = 'pa_' . $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
                $path = $item->storeAs($folderPath, $fileName, 'public');
                $paatts[] = Paatt::create([
                    'name' => $originalName,
                    'size' => $size,
                    'type' => $type,
                    'id_pa' => $pa['id'],
                    'path' => $path
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $pa->pa_id . ' upload success',
            'pa' => $pa,
        ]);
    }

    public function getAllPa($company)
    {
        $pa = Pa::with(['applicant', 'invoice', 'paitem', 'paatt'])
            ->where('pa_id', 'LIKE', '%/' . $company . '/%')
            ->whereIn(DB::raw('(pa_id, rev)'), function ($query) {
                $query->select(DB::raw('pa_id, MAX(rev)'))
                    ->from('pas')
                    ->groupBy('pa_id');
            })->get();

        $allPa = Pa::orderBy('rev')->get()->groupBy('pa_id');

        $pa->transform(function ($p) use ($allPa) {
            $p->history = $allPa[$p->pa_id]
                ->where('rev', '<', $p->rev)
                ->load(['applicant', 'invoice', 'paitem', 'paatt'])
                ->values();
            return $p;
        });
        return response()->json([
            'success' => true,
            'message' => 'Retrieve PA Success',
            'pa' => $pa
        ]);
    }

    public function getPa($id)
    {
        $pa = Pa::with(['applicant', 'invoice', 'paitem', 'paatt'])->where('id', $id)->first();

        if (!$pa) {
            return response()->json([
                'message' => 'PA not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Retrieve PA success',
            'pa' => $pa
        ]);
    }
}
