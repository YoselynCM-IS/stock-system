<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;

class UserController extends Controller
{
    public function index(){
        $users = User::with('role')->orderBy('user_name', 'asc')->withTrashed()->get();
        return response()->json($users);
    }

    public function get_roles(){
        $roles = Role::orderBy('rol', 'asc')->get();
        return response()->json($roles);
    }

    public function set_user(){
        if(env('APP_NAME') == 'MAJESTIC EDUCATION') {
            $i = '89'; $s = 'ME';
        } else {
            $i = '59'; $s = 'OB';
        }

        $cant = User::where('user_name', 'LIKE', '%'.$s.$i.'%')
                ->count();

        $user_name = $s.$i.(str_pad(($cant + 1), 4, '0',STR_PAD_LEFT));
        return response()->json($user_name);
    }

    public function update(Request $request){
        $this->validate($request, [
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'min:5', 'max:60'],
        ]);

        \DB::beginTransaction();
        try {
            $user = User::find($request->id);
            
            $user->update([
                'role_id' => $request->role_id, 
                'name' => $request->name, 
                'user_name' => $request->user_name, 
                'email' => $request->email
            ]);

            if(strlen($request->password) >= 8){
                $user->update([ 'password' => bcrypt($request->password) ]);
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    public function store(Request $request){
        $this->validate($request, [
            'role_id' => ['required'],
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'min:5', 'max:60', 'unique:users'],
            'user_name' => ['required', 'string', 'min:8', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        \DB::beginTransaction();
        try {
            User::create([
                'role_id' => $request->role_id, 
                'name' => $request->name, 
                'user_name' => $request->user_name, 
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        
        return response()->json(true);
    }

    public function delete(Request $request){
        \DB::beginTransaction();
        try {
            User::whereId($request->user_id)->delete();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function restore(Request $request){
        \DB::beginTransaction();
        try {
            $user = User::whereId($request->user_id)
                ->withTrashed()->update([
                    'deleted_at' => null
                ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($user);
    }
}
