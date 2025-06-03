<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invdesc;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Ticket;
use App\Models\Wip;
use App\Models\WipAtt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class WipController extends Controller
{
    public function uploadWipAtt(Request $request)
    {
        $wipAtt = WipAtt::create([
            'id_wip' => $request->id_wip,
            'id_user' => $request->id_user,
            'desc' => $request->desc,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Upload wip attachment success',
            'wipAtt' => $wipAtt
        ]);
    }
    public function uploadFiles(Request $request, $company)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
            'type' => 'required',
            'wip_id' => 'required'
        ]);

        $wip_id_raw = $request->wip_id;
        $type = $request->type;
        $cleanWipId = str_replace([' ', '\\', '/'], '_', $wip_id_raw);
        $file = $request->file('file');
        $timestamp = time();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = $type . '_' . $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
        $folderPath = $company . '/' . $cleanWipId;


        $wip = Wip::where('wip_id', $wip_id_raw)->first();
        $oldFilePath = $wip->$type;
        if ($oldFilePath) {
            Storage::disk('public')->delete($oldFilePath);
        }

        $path = $file->storeAs($folderPath, $filename, 'public');
        $wip->update([
            $type => $path
        ]);
        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'wip' => $wip
        ]);
    }

    public function uploadOtherFiles(Request $request, $company)
    {
        $request->validate([
            'file.*' => 'required|file',
            'wip_id' => 'required'
        ]);

        $wip_id_raw = $request->wip_id;
        $paths = [];

        $wip = Wip::where('wip_id', $wip_id_raw)->first();
        $cleanWipId = str_replace([' ', '\\', '/'], '_', $wip_id_raw);
        $folderPath = $company . '/' . $cleanWipId . '/other';

        foreach ($request->file('files') as $file) {
            $timestamp = time();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $filename = 'other_' . $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
            $path = $file->storeAs($folderPath, $filename, 'public');

            $paths[] = $path;
        }

        $existing = $wip->others;

        if (is_string($existing)) {
            $existing = json_decode($existing, true);
        }
        if (!is_array($existing)) {
            $existing = []; // fallback to empty array
        }
        $merged = array_merge($existing, $paths);

        $wip->update([
            'others' => $merged
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Upload other files success',
            'paths' => $paths
        ]);
    }

    public function uploadPhotoInfo(Request $request, $company)
    {
        $request->validate([
            'photo' => 'required|image',
            'desc' => 'required',
            'id_wip' => 'required',
            'id_att' => 'required',
            'index' => 'required'
        ]);

        $wip = Wip::where('id', $request->id_wip)->first();
        $wipAtt = WipAtt::where('id', $request->id_att)->first();

        $wip_id_raw = $wip->wip_id;
        $cleanWipId = str_replace([' ', '\\', '/'], '_', $wip_id_raw);
        $photo = $request->file('photo');
        $timestamp = time();
        $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $photo->getClientOriginalExtension();
        $fileName = $timestamp . '_' . str_replace(' ', '_', $originalName) . '.' . $extension;
        $folderPath = $company . '/' . $cleanWipId . '/desc';

        $storedPath = $photo->storeAs($folderPath, $fileName, 'public');

        $existingPhotos = is_string($wipAtt->photo) ? json_decode($wipAtt->photo, true) : [];
        $existingDescs = is_string($wipAtt->photo_desc) ? json_decode($wipAtt->photo_desc, true) : [];

        $existingPhotos = $existingPhotos ?? [];
        $existingDescs = $existingDescs ?? [];

        $index = $request->index;

        $existingPhotos[$index] = $storedPath;
        $existingDescs[$index] = $request->desc;

        $wipAtt->photo = json_encode($existingPhotos);
        $wipAtt->photo_desc = json_encode($existingDescs);
        $wipAtt->save();

        return response()->json([
            'success' => true,
            'message' => 'Photo added successfully',
            'wipAtt' => $wipAtt,
        ]);
    }

    public function updateInfo($id, Request $request)
    {
        $request->validate([
            'index' => 'required'
        ]);

        $att = WipAtt::where('id', $id)->first();

        $desc = is_string($att->photo_desc) ? json_decode($att->photo_desc, true) : [];

        $desc[$request->index] = $request->desc;
        $att->photo_desc = json_encode($desc);
        $att->save();

        return response()->json([
            'success' => true,
            'message' => "Update description success",
            'data' => $att
        ]);
    }

    public function acceptWip(Request $request, $id)
    {
        $wip = Wip::where('id', $id)->first();
        $user = Auth::user();

        $quotation = Quotation::where('id_ticket', $wip->id_ticket)->with('quotDesc')->first();
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

        $count = Invoice::distinct('invoice_id')->where('invoice_id', 'LIKE', '%/' . $request->company . '/%')->count('invoice_id');
        $number = $count + 1;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        $month = date('n');
        $year = date('y');
        $invoice_id = 'INV/' . $number . '/' . $request->company . '/' . $romanMonths[$month] . '/' . $year;

        $invoice = Invoice::create([
            'id_user' => $user->id,
            'id_client' => $quotation->id_client,
            'id_ticket' => $quotation->id_ticket,
            'id_quotation' => $quotation->id,
            'invoice_id' => $invoice_id,
            'equipment' => $quotation->equipment,
            'reff_requisition' => $quotation->reff_requisition,
            'disc' => $quotation->disc,
            'disc_type' => $quotation->disc_type,
            'valid_until' => $quotation->valid_until,
            'currency' => $quotation->currency,
            'company' => $quotation->company,
            'rev' => 0,
            'total' => $quotation->total,
            'status' => 'Pending',
            'terms_conditions' => $quotation->terms_conditions
        ]);

        foreach ($quotation->quotDesc as $item) {
            $allInvDesc[] = Invdesc::create([
                'id_invoice' => $invoice->id,
                'id' => $item['id'],
                'desc' => $item['desc'],
                'parent' => $item['parent'],
                'qty' => $item['qty'],
                'unit' => $item['unit'],
                'price' => $item['price'],
                'total' => $item['total'],
                'remark' => $item['remark']
            ]);
        }

        $wip->update([
            'status' => 'Closed'
        ]);
        return response()->json([
            'success' => true,
            'message' => $wip->wip_id . ' accepted',
            'wip' => $wip,
            'invoice' => $invoice,
            'invdesc' => $allInvDesc
        ]);
    }

    public function deleteFiles(Request $request)
    {
        $request->validate([
            'path' => 'required',
            'wip_id' => 'required',
            'type' => 'required'
        ]);

        $wip = Wip::where('id', $request->wip_id)->first();
        if (!$wip) {
            return response()->json([
                'message' => "WIP NOT FOUND " . $request->wip_id
            ]);
        }
        $wip->update([
            $request->type => null
        ]);

        Storage::disk('public')->delete($request->path);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
            'wip' => $wip
        ]);
    }
    public function deleteOtherFiles(Request $request)
    {
        $request->validate([
            'path' => 'required',
            'wip_id' => 'required'
        ]);

        $wip = Wip::where('id', $request->wip_id)->first();
        $others = json_decode($wip->others, true);
        $updated = array_filter($others, function ($item) use ($request) {
            return $item !== $request->path;
        });

        Storage::disk('public')->delete($request->path);

        $wip->update([
            'others' => json_encode(array_values($updated))
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    public function deletePhotoInfo(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'path' => 'required',
        ]);

        $wip_att = WipAtt::where('id', $request->id)->first();
        $photos = json_decode($wip_att->photo, true) ?? [];
        $descs = json_decode($wip_att->photo_desc, true) ?? [];

        $photoIdx = array_search($request->path, $photos, true);
        unset($photos[$photoIdx], $descs[$photoIdx]);

        $photos = array_values($photos);
        $descs = array_values($descs);

        $wip_att->update([
            'photo' => json_encode($photos),
            'photo_desc' => json_encode($descs)
        ]);

        Storage::disk('public')->delete($request->path);

        return response()->json([
            'success' => true,
            'message' => 'Delete info success',
            'wip_att' => $wip_att
        ]);
    }
    public function getAllWip($company)
    {
        $wip = Wip::with([
            'ticket.assign',
            'ticket.client',
            'ticket.quotation' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'wipAtt' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->where("wip_id", 'LIKE', '%/' . $company . '/%')->get();

        return response()->json([
            'success' => true,
            'message' => 'Retrieve work in progress success',
            'wip' => $wip,
            'company' => $company
        ]);
    }
    public function getWipById($id)
    {
        $wip = Wip::where('id', $id)->with('wipAtt.user')
            ->first();

        return response()->json([
            'success' => true,
            'message' => "Retrieve " . $wip->wip_id . " success",
            'wip' => $wip
        ]);
    }

    public function wipAge($company)
    {
        $wipRecords = Wip::where('status', '=', 'On Going')
            ->where('wip_id', 'LIKE', '%/' . $company . '/%')
            ->with(['ticket.quotation'])
            ->get();
        $data = [];

        foreach ($wipRecords as $wip) {
            $curr = "";
            $total = 0;
            $disc_type = "";
            $disc = 0;
            foreach ($wip->ticket->quotation as $quot) {
                if ($quot->status === 'Accepted') {
                    $total = $quot->total;
                    $disc_type = $quot->disc_type;
                    $disc = $quot->disc;
                    $curr = $quot->currency;
                }
            }

            $open = Carbon::parse($wip->updated_at);
            $now = Carbon::now();

            $age = $open->diffInDays($now);

            $tax = 0;
            $afterTax = 0;
            $totalDisc = 0;
            if ($disc) {
                if ($disc_type === "Percentage") {
                    $totalDisc = $total * ($disc / 100);
                } else {
                    $totalDisc = $disc;
                }
            }
            $total = $total - $totalDisc;
            $tax = $total * (11 / 100);
            $afterTax = $total + $tax;

            $data[] = [
                'age' => $age,
                'sum' => $afterTax,
                'disc' => $totalDisc,
                'curr' => $curr
            ];
        }

        return response()->json([
            'success' => true,
            'message' => "Retrieve wip age success",
            'wip' => $data,
        ]);
    }
}
