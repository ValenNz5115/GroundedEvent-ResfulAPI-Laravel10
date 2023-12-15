<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function createEvent(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name_event' => 'required',
            'description' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'price' => 'required|numeric',
            'image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048'
        ], [
            'name_event.required' => 'Name event is required',
            'description.required' => 'Description is required',
            'start_date.required' => 'Start date is required',
            'start_date.date_format' => 'Start date must be in the format Y-m-d',
            'end_date.required' => 'End date is required',
            'end_date.date_format' => 'End date must be in the format Y-m-d',
            'end_date.after_or_equal' => 'End date must be equal to or after start date',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a numeric value',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpg, png, jpeg, gif, svg.',
            'image.max' => 'The image may not be greater than 2048 kilobytes.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {
            $imagePath = null;
            if ($req->hasFile('image')) {
                $images = $req->file('image');
                $name = time() . '.' . $images->getClientOriginalExtension();
                $imagePath = $images->storeAs('public/image/events', $name);
            }

            $event = Event::create([
                'name_event' => $req->input('name_event'),
                'description' => $req->input('description'),
                'start_date' => $req->input('start_date'),
                'end_date' => $req->input('end_date'),
                'price' => $req->input('price'),
                'image' => $imagePath,
            ]);

            if ($event) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added a new event',
                    'data' => $event
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add a new event'
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

    public function allEvent(Request $req)
    {
        try {
            $query = Event::query();

            if ($req->has('name_event')) {
                $query->where('name_event', 'like', '%' . $req->input('name_event') . '%');
            }

            $sortBy = $req->input('sort_by', 'event_id');
            $sortOrder = $req->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $req->input('per_page', 10);
            $event = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'event retrieved successfully',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getEvent(Request $req, $event_id)
    {
        try {
            $event = Event::findOrFail($event_id);

            return response()->json([
                'status' => 'success',
                'message' => 'event retrieved successfully',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'event not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function updateEvent(Request $req, $event_id)
    {
        $data = Event::find($event_id);

        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'event data not found']);
        }

        $validator = Validator::make($req->all(), [
            'name_event' => 'required',
            'description' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'price' => 'required|numeric',
            'image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048'
        ], [
            'name_event.required' => 'Name event is required',
            'description.required' => 'Description is required',
            'start_date.required' => 'Start date is required',
            'start_date.date_format' => 'Start date must be in the format Y-m-d',
            'end_date.required' => 'End date is required',
            'end_date.date_format' => 'End date must be in the format Y-m-d',
            'end_date.after_or_equal' => 'End date must be equal to or after start date',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a numeric value',
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
                Storage::delete('public/image/events/' . $oldImage);

                $file = $req->file('image');
                $nama = time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('public/image/events/', $nama);
                $imageName = basename($imagePath);
            } else {
                $imageName = $oldImage;
            }

            $event = [
                'name_event' => $req->input('name_event'),
                'description' => $req->input('description'),
                'start_date' => $req->input('start_date'),
                'end_date' => $req->input('end_date'),
                'price' => $req->input('price'), // Added price field
                'image' => $imagePath,
            ];

            $result = $data->update($event);

            if ($result) {
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Successfully updated a new event',
                        'data' => $event
                    ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to add a new event']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteEvent($event_id)
    {
        try {
            $event = Event::findOrFail($event_id);



            Storage::delete('public/image/events/' . $event->image);

            $deleteevent = $event->delete();

            if ($deleteevent) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'event deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete event',
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
