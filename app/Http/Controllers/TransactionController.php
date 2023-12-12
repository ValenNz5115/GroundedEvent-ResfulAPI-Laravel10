<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function createTransaction(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'customer_id' => 'required|exists:customers,customer_id',
            'transac_id' => 'required|exists:transacs,transac_id',
        ], [
            'customer_id.required' => 'Customer ID is required',
            'customer_id.exists' => 'Invalid Customer ID. Customer does not exist.',
            'transac_id.required' => 'transac ID is required',
            'transac_id.exists' => 'Invalid transac ID. transac does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $now = date('Y-m-d');

            $existingtransaction = transaction::where('customer_id', $req->get('customer_id'))
                ->where('transac_id', $req->get('transac_id'))
                ->where('status_ordered', 'order')
                ->first();

            if ($existingtransaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Customer already has an active transaction for this transac.',
                ], 422);
            }

            $transaction = Transaction::create([
                'customer_id'   => $req->get('customer_id'),
                'transac_id'      => $req->get('transac_id'),
                'transaction_date'  => $now,
                'status'       => 'process',
                'status_payment'       => 'waiting',
            ]);

            if ($transaction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new transaction',
                    'data' => $transaction,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new transaction',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function allLoan()
    {
        try {
            // Paginate the results
            $loan = DB::table('loans')
                ->join('students', 'loans.student_id', '=', 'students.student_id')
                ->join('books', 'loans.book_id', '=', 'books.book_id')
                ->join('classrooms', 'students.class_id', '=', 'classrooms.class_id')
                ->orderBy('loans.loan_id')
                ->paginate(6);

            return response()->json([
                'status' => 'success',
                'message' => 'Loan data retrieved successfully',
                'data' => $loan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }   

    public function updateTransaction(Request $req, Transaction $transaction_id)
    {
        $validator = Validator::make($req->all(), [
            'status_ordered' => 'required|in:process,finished,cancelled',
            'status_payment' => 'required|in:waiting,paid',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {
            if (!$transaction_id->exists) {
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            $statusPayment = now();
            $statusOrdered = $transaction_id->transaction_date;
            $deadline = Carbon::parse($statusOrdered)->diffInDays($statusPayment);
            $maxPayment = 1;

            $statusPayment = $req->input('status_payment', $deadline <= $maxPayment ? 'waiting' : 'paid');

            $statusOrdered = $req->input('status_ordered', $statusPayment === 'waiting' && $deadline <= $maxPayment ? 'cancelled' : ($statusPayment === 'waiting' ? 'process' : 'finished'));

            $returnDate = $statusPayment === 'paid' ? now() : null;

            $paymentDate = $req->input('payment_date', null);

            $transaction_id->update([
                'return_date' => $returnDate,
                'status' => $statusOrdered,
                'status_payment' => $statusPayment,
                'payment_date' => $statusPayment === 'paid' ? $paymentDate : null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully update',
                'data' => $transaction_id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while processing your request', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteTransaction($transaction_id)
    {
        try {
            // Find the Transaction to be deleted
            $transaction = Transaction::findOrFail($transaction_id);

            // // Check if there are associated records (e.g., students)
            // if ($transaction->students()->exists()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Cannot delete Transaction with associated students',
            //     ], 422); // 422 Unprocessable Entity
            // }

            // Perform the delete operation
            $deleteTransaction = $transaction->delete();

            // Check if the delete operation was successful
            if ($deleteTransaction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaction deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete Transaction',
                ]);
            }
        } catch (\Exception $e) {
            // Handle unexpected exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }


}
