<?php

namespace App\Http\Controllers;
use App\Events\Chat;
use App\Models\QuickReply;
use Illuminate\Http\Request;

class chatController extends Controller
{
    public function index(){
        return view('welcome');
    }
      public function notFound(){
         abort(404, 'Not Found');
    }
 public function chat(Request $request){
      $request->validate([
        'username'=>'required',
      ]);
      $username=$request->username;
      $quickReplies = QuickReply::all();
      return view('chat')->with(['name'=>$username, 'quickReplies'=>$quickReplies]);
    }
    public function broadcastChat(Request $request){
      $request->validate([
        'username'=>'required',
        'msg'=>'required'
      ]);
       event(new Chat($request->username, $request->msg));
       return response()->json($request->all());
    }
}
