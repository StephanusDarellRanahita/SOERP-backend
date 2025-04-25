<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\Ticket;

class TicketController extends Controller
{
    public function uploadTicket(Request $request, $company)
    {
        $user = Auth::user();

        $request->validate([
            'id_client' => 'required',
            'issue' => 'required|string',
            'assign' => 'required',
            'company' => 'required|string',
            'type' => 'required|string'
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

        $count = Ticket::latest()->where('ticket_id', 'LIKE', '%/' . $company . '/%')->first();

        if ($count && preg_match('/TCK\/(\d+)\//', $count->ticket_id, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        $month = date('n');
        $year = date('y');

        $ticket_id = 'TCK/' . $number . '/' . $request->company . '/' . $romanMonths[$month] . '/' . $year;

        $ticket = Ticket::create([
            'id_user' => $user->id,
            'id_client' => $request->id_client,
            'ticket_id' => $ticket_id,
            'issue' => $request->issue,
            'assign' => $request->assign,
            'created' => Carbon::now(),
            'status' => 'Open',
            'type' => $request->type
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Upload new ticket success',
            'ticket' => $ticket
        ]);
    }


    public function getAllTicket($company)
    {
        $ticket = Ticket::with(['user', 'client', 'assign', 'quotation'])->where('ticket_id', 'LIKE', '%/' . $company . '/%')->get();
        $sumTicket = Ticket::where('ticket_id', 'LIKE', '%/' . $company . '/%')->count();
        $tktOpen = Ticket::where('status', 'Open')->where('ticket_id', 'LIKE', '%/' . $company . '/%')->count();
        $tktQuot = Ticket::where('status', 'Quotation')->where('ticket_id', 'LIKE', '%/' . $company . '/%')->count();
        $tktCanceled = Ticket::where('status', 'Canceled')->where('ticket_id', 'LIKE', '%/' . $company . '/%')->count();
        $tktClose = Ticket::where('status', 'Closed')->where('ticket_id', 'LIKE', '%/' . $company . '/%')->count();
        return response()->json([
            'success' => true,
            'message' => 'Retrieve all ticket success',
            'ticket' => $ticket,
            'sum' => $sumTicket,
            'open' => $tktOpen,
            'quot' => $tktQuot,
            'canceled' => $tktCanceled,
            'close' => $tktClose
        ]);
    }
}
