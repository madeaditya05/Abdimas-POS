<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class PegawaiController extends Controller
{
    public function index(){ $kasir = User::where('user_group','kasir')->get(); return view('employees.index',compact('kasir')); }
public function store(Request $r){
  $v = $r->validate([
    'name'=>'required','email'=>'required|email|unique:users',
    'password'=>'required|min:6'
  ]);
  User::create([
    'name'=>$v['name'],'email'=>$v['email'],
    'password'=>bcrypt($v['password']),'user_group'=>'kasir'
  ]);
  return back()->with('success','Kasir dibuat');
}
public function update(Request $r, User $employee){
  $data = $r->validate(['name'=>'required','email'=>'required|email|unique:users,email,'.$employee->id]);
  if($r->filled('password')) $data['password']=bcrypt($r->password);
  $employee->update($data); return back()->with('success','Kasir diupdate');
}
public function destroy(User $employee){ $employee->delete(); return back()->with('success','Kasir dihapus'); }


}