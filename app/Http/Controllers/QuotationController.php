<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Quotdesc;
use App\Models\Ticket;
use App\Models\Wip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function uploadQuotation(Request $request)
    {
        $request->validate([
            'payload' => 'required'
        ]);

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
        $hasTicket = Quotation::where('id_ticket', '=', $request->payload['id_ticket'])->latest()->first();
        $user = Auth::user();
        if (!$hasTicket) {
            $rev = 0;
            $count = Quotation::distinct('quot_id')->where('quot_id', 'LIKE', '%/' . $request->payload['company'] . '%/')->count('quote_id');
            $number = $count + 1;
            $number = str_pad($number, 3, '0', STR_PAD_LEFT);
            $month = date('n');
            $year = date('y');
            $quot_id = 'QUO/' . $number . '/' . $request->payload['company'] . '/' . $romanMonths[$month] . '/' . $year;
        } else {
            $rev = $hasTicket->rev + 1;
            $quot_id = $hasTicket->quot_id;
            $hasTicket->update([
                'status' => "Revision"
            ]);
        }

        $valid_request = (int) $request->payload['valid_until'];

        if ($valid_request === 0) {
            $valid_until = Carbon::now()->addDays(30)->format('Y-m-d');
        } else {
            $valid_until = Carbon::now()->addDays($valid_request)->format('Y-m-d');
        }

        $quotation = Quotation::create([
            'id_user' => $user->id,
            'id_client' => $request->payload['id_client'],
            'id_ticket' => $request->payload['id_ticket'],
            'quot_id' => $quot_id,
            'equipment' => $request->payload['equipment'],
            'reff_requisition' => $request->payload['reff_requisition'],
            'disc' => $request->payload['disc'],
            'disc_type' => $request->payload['disc_type'],
            'valid_until' => $valid_until,
            'currency' => $request->payload['currency'],
            'company' => $request->payload['company'],
            'rev' => $rev,
            'total' => $request->payload['total'],
            'status' => 'Pending',
            'terms_conditions' => $request->payload['terms_conditions']
        ]);
        $allQuotDecs = [];

        foreach ($request->payload['description'] as $item) {
            $allQuotDecs[] = Quotdesc::create([
                'id_quot' => $quotation['id'],
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

        $ticket = Ticket::where('id', '=', $request->payload['id_ticket'])->first();

        if ($ticket) {
            $ticket->update([
                'status' => 'Quotation'
            ]);
        } else {
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Upload quotation success',
            'quotation' => $quotation,
            'quotdesc' => $allQuotDecs,
            'ticket' => $ticket,
            'rev' => $rev,
            'hasTicket' => $hasTicket
        ]);
    }

    public function getAllQuotation($company)
    {
        $quotation = Quotation::with(['client', 'ticket'])->where('quot_id', 'LIKE', '%/' . $company . '/%')
            ->whereIn(DB::raw('(quot_id, rev)'), function ($query) {
                $query->select(DB::raw('quot_id, MAX(rev)'))
                    ->from('quotations')
                    ->groupBy('quot_id');
            })->orderByDesc('quot_id')->get();

        $allQuotations = Quotation::orderBy('rev')->get()->groupBy('quot_id');

        $quotation->transform(function ($quot) use ($allQuotations) {
            $quot->history = $allQuotations[$quot->quot_id]
                ->where('rev', '<', $quot->rev)
                ->load(['client', 'ticket'])
                ->values();
            return $quot;
        });

        return response()->json([
            'success' => true,
            'message' => 'Retrieve quotation success',
            'quotation' => $quotation
        ]);
    }

    public function getQuotation($id)
    {
        $quotation = Quotation::with("quotDesc")
            ->with("ticket.assign")
            ->with("client")
            ->with("user")
            ->where('id', '=', $id)->first();

        return response()->json([
            'success' => true,
            'message' => "Retrieve quotation success",
            'quotation' => $quotation
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        $quotation = Quotation::find($id);
        $quotation->update([
            'status' => $request->status
        ]);

        $ticket = Ticket::find($quotation->id_ticket);
        if ($request->status === 'Accepted') {
            $ticket->update([
                'status' => "WIP",
            ]);

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
            $count = Wip::distinct('wip_id')->where('wip_id', 'LIKE', '%/' . $request->company . '%/')->count('wip_id');
            $number = $count + 1;
            $number = str_pad($number, 3, '0', STR_PAD_LEFT);
            $month = date('n');
            $year = date('y');
            $wip_id = 'WIP/' . $number . '/' . $request->company . '/' . $romanMonths[$month] . '/' . $year;

            $wip = Wip::create([
                'id_ticket' => $ticket->id,
                'wip_id' => $wip_id,
                'status' => 'On Going'
            ]);

            return response()->json([
                'success' => true,
                'message' => $quotation . " status has been changed to" . $request->status,
                'quotation' => $quotation,
                'wip' => $wip
            ]);
        } else if ($request->status === 'Canceled') {
            $ticket->update([
                'status' => "Canceled"
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => $quotation . " status has been changed to" . $request->status,
            'quotation' => $quotation
        ]);
    }
}
