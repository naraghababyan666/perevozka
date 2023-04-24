<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function create(Request $request){
        $data = Validator::make($request->all(), [
            'company_id' => "required",
            'first_name' => 'required',
            'last_name' => 'required'
        ]);
        if ($data->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $data->errors()
            ])->header('Status-Code', 200);
        }

        $manager = new Manager();
        $manager->company_id = $data->validated()['company_id'];
        $manager->first_name = $data->validated()['first_name'];
        $manager->last_name = $data->validated()['last_name'];
        if(isset($request->all()['phone_number'])){
            $manager->phone_number = $data->validated()['phone_number'];
        }
        $manager->save();
        $lastManager = Manager::query()->find($manager->id);

        return response()->json(['success' => true, 'manager' => $lastManager]);

    }

    public function delete($id){
        Manager::query()->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Manager successfully deleted']);

    }
}
