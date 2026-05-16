<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->addresses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'          => 'required|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone'          => 'required|string|regex:/^[6-9][0-9]{9}$/',
            'apartment'      => 'required|string|max:255',
            'street'         => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'zip_code'       => 'nullable|string|max:20',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ]);

        $address = $request->user()->addresses()->create($data);

        return response()->json($address, 201);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'label'          => 'sometimes|string|max:100',
            'recipient_name' => 'sometimes|string|max:255',
            'phone'          => 'sometimes|string|regex:/^[6-9][0-9]{9}$/',
            'apartment'      => 'sometimes|required|string|max:255',
            'street'         => 'sometimes|nullable|string|max:255',
            'city'           => 'sometimes|nullable|string|max:100',
            'state'          => 'sometimes|nullable|string|max:100',
            'zip_code'       => 'sometimes|nullable|string|max:20',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ]);

        $address->update($data);

        return response()->json($address);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted.']);
    }
}
