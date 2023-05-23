<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function create(Request $request){
        $data = Validator::make($request->all(), [
            'FullName' => 'required',
            'phone_number' => 'required'
        ]);
        if ($data->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $data->errors()
            ])->header('Status-Code', 200);
        }
        if(Company::query()->where('phone_number', $data->validated()['phone_number'])->exists()){
            return response()->json(['success' => false, 'message' => 'Phone number is exists']);
        }

        $manager = new Manager();
        $manager->company_id = Auth::id();
        $manager->FullName = $data->validated()['FullName'];
        $manager->phone_number = $data->validated()['phone_number'];
        $manager->save();
        $lastManager = Manager::query()->find($manager->id);

        return response()->json(['success' => true, 'manager' => $lastManager]);

    }

    public function delete($id){
        Manager::query()->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Manager successfully deleted']);

    }
}
