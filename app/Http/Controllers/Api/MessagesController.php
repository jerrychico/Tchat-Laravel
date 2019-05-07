<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jerry-107
 * Date: 06/05/2019
 * Time: 09:00
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessagesController  extends Controller
{
    public function read (Request $request,Message $message) {
        $message->update([
            'read_at' => Carbon::now()
        ]);
        return ['success' => 1];
    }
}
