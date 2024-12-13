<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChannelController extends Controller
{
    private $apiToken = 'uMxPHon+J28pd17nie3qeU+kF7gUulWjb2UF5SRFr4rSIhmLHLwuL6TjY92JGxsx'; // Reemplaza con tu token de acceso

    public function index(){
        $token = $this->apiToken;
        return view('admin.channel.index', compact('token'));
    }
}
