<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Events\TcpServerStatusUpdated;


class ReverbController extends Controller{
    public function index(){
        $status = Redis::get('tcp_server_status') ?? 'stopped';
        return view('ethereal.index', compact('status'));
    }
    public function control(Request $request){
        $action = $request->input('action');
        Redis::publish('tcp_server_control', $action);// Redis 廣播
        // broadcast(new TcpServerStatusUpdated($action));// Reverb 廣播
        return redirect('ethereal.index');
    }
}