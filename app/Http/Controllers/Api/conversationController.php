<?php
namespace App\Http\Controllers\Api;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Repository\conversationRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class conversationController extends Controller {

    /**
     * @var conversationRepository
     */
    private $repository;

    public function __construct(conversationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index (Request $request) {
        $conversations = $this->repository->getConversations($request->user()->id);
        $unread = $this->repository->unreadCount($request->user()->id);
        foreach ($conversations as $conversation) {
            if (isset($unread[$conversation->id])){
                $conversation->unread = $unread[$conversation->id];
            } else {
                $conversation->unread = 0;
            }
        }

        return [
            'conversations'=> $conversations
        ];
    }

    public function show (Request $request, User $user) {
        $messagesQuery = $this->repository->getMessageFor($request->user()->id, $user->id);
        $count = null;
        if ($request->get('before')){
            $messagesQuery = $messagesQuery->where('created_at', '<',$request->get('before'));
        } else {
            $count = $messagesQuery->count();
        }
        $messages = $messagesQuery->limit(10)->get();
        $update = false;
        foreach ($messages as $message) {
            if ($message->read_at === null && $message->to_id === $request->user()->id){
                $message->read_at = Carbon::now();
                if ($update === false){
                    $this->repository->readAllFrom($message->from_id, $message->to_id);
                }
                $update = true;
            }
        }
        return [
            'messages' => array_reverse($messages->toArray()),
            'count' => $count
        ];
    }
    public function store (User $user, StoreMessageRequest $request) {
        $messages = $this->repository->CreateMessage(
            $request->get('content'),
            $request->user()->id,
            $user->id
        );
        broadcast(new NewMessage($messages));
        return [
            'messages' => $messages
        ];
    }
}
