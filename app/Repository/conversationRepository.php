<?php

namespace App\Repository;

use App\User;
use Carbon\Carbon;
use App\Message;
use Illuminate\Database\Eloquent\Builder;

class conversationRepository {

    /**
     * @var User
     */
    private $user;
    /**
     * @var Message
     */
    private $message;

    public function __construct(User $user,Message $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function getConversations(int $userId) {
        $conversations = $this->user->newQuery()
            ->select('name', 'id')
            ->where('id', '!=', $userId)
            ->get();

       /* $unread = $this->unreadCount($userId);
        foreach ($conversations as $conversation){
            if (isset($unread[$conversation->id])){
                $conversation->unread = $unread[$conversation->id];
            } else {
                $conversation->unread = 0;
            }
        }*/

        return $conversations;
    }


    /**
     * @param string $content
     * @param int $from
     * @param int $to
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function CreateMessage (string $content, int $from, int $to) {
        return $this->message->newQuery()->create(
            [
                'content' => $content,
                'from_id' => $from,
                'to_id'   => $to,
                'created_at' => Carbon::now()
            ]

        );
    }

    /**
     * @param int $from
     * @param int $to
     * @return Builder
     */
    public function getMessageFor (int $from, int $to): Builder {
        return $this->message->newQuery()
            ->whereRaw("((from_id = $from AND to_id = $to) OR (from_id = $to AND to_id = $from))")
            ->orderBy('created_at', 'desc')
            ->with([
                'from' => function ($query) { return $query->select('name','id');}
            ]);
    }

    /**
     * recuperer le nombre de messages nom lues par from_id
     * @param int $userId
     * @return \Illuminate\Support\Collection|static
     */
    public function unreadCount (int $userId){
        return $this->message->newQuery()
            ->where('to_id', $userId)
            ->groupBy('from_id')
            ->selectRaw('from_id, COUNT(id) as count')
            ->whereRaw('read_at IS NULL')
            ->get()
            ->pluck('count','from_id');
    }

    /**
     * Marque tous les messages de cet utilisateur comme lu
     * @param int $form
     * @param int $to
     */
    public function readAllFrom(int $form, int $to)
    {
        $this->message->where('from_id', $form)->where('to_id', $to)->update(['read_at'=> Carbon::now()]);
    }
}
