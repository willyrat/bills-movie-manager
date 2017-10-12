@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(Auth::check())
                        You are logged in!
                    @endif


                    @if(Auth::guest())
                        you are guest
                    @endif
                </div>
                
                <usermovies></usermovies>
                <!-- <div class="container">

                    <h2>Your Movies</h2>

                    <table class="table table-bordered">

                        <tr>

                            <th>Id</th>
                            <th>Title</th>
                            <th>Length</th>
                            <th>Year Released</th>
                            <th>Rating</th>

                        </tr>

                        @if($userMovies->count() > 0)

                            @foreach($userMovies as $userMovie)

                                <tr>

                                    <td>{{ $userMovie->id }}</td>
                                    <td>{{ $userMovie->title }}</td>
                                    <td>{{ $userMovie->length }}</td>
                                    <td>{{ $userMovie->releaseYear }}</td>
                                    <td>{{ $userMovie->rating }}</td>

                                </tr>

                            @endforeach

                        @endif

                    </table>

                    

                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection
