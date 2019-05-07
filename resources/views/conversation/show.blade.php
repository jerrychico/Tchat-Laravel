@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            @include('conversation.users',['users' => $users,'unread' => $unread])
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">{{ $user->name }}</div>
                    <div class="card-body conversation">
                        @if($messages->hasMorePages())
                            <div class="text-center">
                                <a href="{{ $messages->nextPageUrl() }}" class="btn btn-light">
                                    voir les messages precedents
                                </a>
                            </div>
                        @endif
                        @foreach(array_reverse($messages->items()) as $message)
                            <div class="row">
                                <div class="col-md-10 {{ $message->from->id !== $user->id ? 'offset-md-2 text-right' : '' }}">
                                    <p>
                                        <strong>{{ $message->from->id !== $user->id ? 'Moi' :$message->from->name }}</strong><br>
                                        {!! nl2br(e($message->content))  !!}
                                    </p>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                        @if($messages->previousPageUrl())
                            <div class="text-center">
                                <a href="{{ $messages->previousPageUrl() }}" class="btn btn-light">
                                    voir les messages suivant
                                </a>
                            </div>
                        @endif
                        <form action="" method="post">
                            {{ csrf_field() }}
                            <div class="form-group ">
                                <textarea name="content" placeholder="Ecrivez votre message" class="form-control {{ $errors->has('content') ? 'is-invalid' : '' }}"></textarea>
                                @if($errors->has('content'))
                                    <div class="invalid-feedback">{{ implode(',' , $errors->get('content')) }}</div>
                                @endif
                            </div>
                            <button class="btn btn-primary" type="submit">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
