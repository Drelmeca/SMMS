<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SampleCrud;

class SampleCrudController extends Controller
{
    
    public function index()
    {
        $persons = SampleCrud::all();

        return Inertia::render('SampleCrud/Index', [
            'persons' => $persons,
        ]);
    }

    public function create()
    {
        return inertia('SampleCrud/Edit', ['person' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
        ]);

        if ($request->input('id')) {
            // Update existing record
            $person = SampleCrud::findOrFail($request->input('id'));
            $person->name = $request->input('name');
            $person->email = $request->input('email');
            $person->phone = $request->input('phone');
            $person->save();
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
            ]);
        }else {
            // Create new record
            $requestCert = new SampleCrud();
            $requestCert->name = $request->input('name');
            $requestCert->email = $request->input('email');
            $requestCert->phone = $request->input('phone');
            $requestCert->save();
            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully.',
            ]);
        }

    }

    public function edit($id)
    {
        //$person = SampleCrud::findOrFail(id: $id);
        
        $person = SampleCrud::where('id', $id)->first();
        return inertia('SampleCrud/Edit', ['person' => $person, 'mode' => 'edit']);
    }

  

    public function destroy($id)
    {
        // Delete the record
        // Redirect or return a response
    }
}
