<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jerry-107
 * Date: 06/05/2019
 * Time: 09:08
 */

namespace App\Policies;


use App\Message;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    public function read (User $user,Message $message){
        return $user->id === $message->to_id;
    }

}
