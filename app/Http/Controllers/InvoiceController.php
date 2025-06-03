<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invdesc;
use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function uploadInvoice(Request $request)
    {
        $request->validate([
            'payload' => 'required'
        ]);

        $hasTicket = Invoice::where('id_ticket', '=', $request->payload['id_ticket'])->latest()->first();
        $quotation = Quotation::where('id_ticket', $request->payload['id_ticket'])->with('quotDesc')->first();
        $user = Auth::user();
        $rev = $hasTicket->rev + 1;
        $invoice_id = $hasTicket->invoice_id;

        $invoice = Invoice::create([
            'id_user' => $user->id,
            'id_client' => $request->payload['id_client'],
            'id_ticket' => $request->payload['id_ticket'],
            'id_quotation' => $quotation->id,
            'invoice_id' => $invoice_id,
            'equipment' => $request->payload['equipment'],
            'reff_requisition' => $request->payload['reff_requisition'],
            'disc' => $request->payload['disc'],
            'disc_type' => $request->payload['disc_type'],
            'valid_until' => $hasTicket->valid_until,
            'currency' => $request->payload['currency'],
            'company' => $request->payload['company'],
            'rev' => $rev,
            'total' => $request->payload['total'],
            'status' => 'Pending',
            'terms_conditions' => $request->payload['terms_conditions']
        ]);
        $allInvoiceDesc = [];

        foreach ($request->payload['description'] as $item) {
            $allInvoiceDesc[] = Invdesc::create([
                'id_invoice' => $invoice['id'],
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

        $hasTicket->update([
            'status' => "Revision"
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Invoice successfully revised',
            'invoice' => $invoice,
            'invdesc' => $allInvoiceDesc
        ]);
    }

    public function acceptInvoice($id)
    {
        $invoice = Invoice::where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'message' => "Invoice not found"
            ]);
        }

        $invoice->update([
            'status' => "Accepted"
        ]);
        return response()->json([
            'success' => true,
            'message' => "Invoice accepted successfully",
            'invoice' => $invoice
        ]);
    }
    public function getAllInvoice($company)
    {
        $invoice = Invoice::with(['user', 'client', 'ticket.assign', 'invdesc'])
            ->where('invoice_id', 'LIKE', '%/' . $company . '/%')
            ->whereIn(DB::raw('(invoice_id, rev)'), function ($query) {
                $query->select(DB::raw('invoice_id, MAX(rev)'))
                    ->from('invoices')
                    ->groupBy('invoice_id');
            })->get();
        $allInvoice = Invoice::orderBy('rev')->get()->groupBy('invoice_id');
        $invoice->transform(function ($inv) use ($allInvoice) {
            $inv->history = $allInvoice[$inv->invoice_id]
                ->where('rev', '<', $inv->rev)
                ->load(['user', 'client', 'ticket'])
                ->values();
            return $inv;
        });

        $accInvoice = Invoice::with([
            'user',
            'client',
            'ticket.assign',
            'invdesc' => function ($query) {
                $query->whereNull('parent');
            }
        ])->where('status', 'Accepted')->get();

        return response()->json([
            'success' => true,
            'message' => 'Retrieve invoice success',
            'invoice' => $invoice,
            'accInvoice' => $accInvoice
        ]);
    }

    public function getInvoice($id)
    {
        $invoice = Invoice::with("ticket.assign")
            ->with("invdesc")
            ->with("client")
            ->with('user')
            ->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => "Retrieve invoice success",
            'invoice' => $invoice
        ]);
    }
}
