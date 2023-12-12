<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function createCustomer(Request $req)
{
    $validator = Validator::make($req->all(), [
        'username' => 'required',
        'email' => 'required|email|unique:customers,email',
        'password' => 'required',
        'phone' => 'required',
        'ttl' => 'required|date_format:Y-m-d',
        'city' => 'required',
        'company' => 'required',
        'gender' => 'required|in:male,female',
        'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
    ], [
        'username.required' => 'Username is required',
        'email.required' => 'Email is required',
        'email.email' => 'Invalid email format',
        'email.unique' => 'Email is already taken',
        'password.required' => 'Password is required',
        'phone.required' => 'Phone is required',
        'ttl.required' => 'Start date is required',
        'ttl.date_format' => 'Start date must be in the format Y-m-d',
        'city.required' => 'City is required',
        'company.required' => 'Company is required',
        'gender.required' => 'Gender is required',
        'gender.in' => 'Invalid gender value',
        'image.required' => 'Image is required',
        'image.image' => 'The file must be an image.',
        'image.mimes' => 'The image must be a file of type: jpg, png, jpeg, gif, svg.',
        'image.max' => 'The image may not be greater than 2048 kilobytes.',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()], 400);
    }

    try {
        $imagePath = null;

        if ($req->hasFile('image')) {
            $file = $req->file('image');
            $name = time() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('public/image/customers', $name);
        }

        $customer = Customer::create([
            'username' => $req->input('username'),
            'email' => $req->input('email'),
            'password' => bcrypt($req->input('password')),
            'phone' => $req->input('phone'),
            'ttl' => $req->input('ttl'),
            'city' => $req->input('city'),
            'company' => $req->input('company'),
            'gender' => $req->input('gender'),
            'image' => $imagePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully added a new customer',
            'data' => $customer
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while processing your request',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    public function allCustomer(Request $req)
    {
        try {
            $query = Customer::query();

            if ($req->has('username')) {
                $query->where('username', 'like', '%' . $req->input('username') . '%');
            }

            $sortBy = $req->input('sort_by', 'customer_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $req->input('per_page', 10);
            $customer = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'customer retrieved successfully',
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomer(Request $req, $customer_id)
    {
        try {
            $customer = customer::findOrFail($customer_id);

            return response()->json([
                'status' => 'success',
                'message' => 'customer retrieved successfully',
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'customer not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function updateCustomer(Request $req, $customer_id)
    {
        $data = customer::find($customer_id);

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'customer data not found']);
        }

        $validator = Validator::make($req->all(), [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'phone' => 'required',
            'ttl' => 'required|date_format:Y-m-d',
            'city' => 'required',
            'company' => 'required',
            'gender' => 'required|in:male,female',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ], [
            'username.required' => 'Username is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'password.required' => 'Password is required',
            'phone.required' => 'Phone is required',
            'ttl.required' => 'Start date is required',
            'ttl.date_format' => 'Start date must be in the format Y-m-d',
            'city.required' => 'City is required',
            'company.required' => 'Company is required',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Invalid gender value',
            'image.required' => 'Image is required',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpg, png, jpeg, gif, svg.',
            'image.max' => 'The image may not be greater than 2048 kilobytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {

            $oldImage = $data->image;

            if ($req->hasFile('image')) {
                Storage::delete('public/image/customers/' . $oldImage);

                $file = $req->file('image');
                $nama = time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('public/image/customers/', $nama);
                $imageName = basename($imagePath);
            } else {
                $imageName = $oldImage;
            }

            $customer = [
                'username' => $req->input('username'),
                'email' => $req->input('email'),
                'password' => bcrypt($req->input('password')),
                'phone' => $req->input('phone'),
                'ttl' => $req->input('ttl'),
                'city' => $req->input('city'),
                'company' => $req->input('company'),
                'gender' => $req->input('gender'),
                'image' => $imagePath,
            ];

            $result = $data->update($customer);

            if ($result) {
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Successfully updated a new customer',
                        'data' => $customer
                    ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to add a new customer']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletecustomer($customer_id)
    {
        try {


            Storage::delete('public/image/customers/' . $customer->image);

            $deletecustomer = $customer->delete();

            if ($deletecustomer) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'customer deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete customer',
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

}
