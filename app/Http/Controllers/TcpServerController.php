<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Events\ReverbServerStatusUpdated;


class TcpServerController extends Controller{
    public function index(){
        $status = Redis::get('tcp_status') ?? 'stopped';
        return view('tcp-server', compact('status'));
    }
    public function control(Request $request){
        $action = $request->input('action');
        Redis::publish('tcp_server_control', $action);// Redis 廣播
        // broadcast(new ReverbServerStatusUpdated($action));// Reverb 廣播
        
        return redirect('/tcp-server');
    }
}