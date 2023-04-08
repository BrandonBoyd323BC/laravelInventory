@extends('layout')
@section('content')   
    <h1>{{$heading}}</h1>

    @if(count($links) == 0) {
            <h2>
                {{$message}}
            </h2>
        }
    @endif

    @foreach ($links as $link)
        <h2>
           <a href="{{$link['link']}}">
                {{$link['link']}}
            </a>
        </h2>
        <p>
            {{$link['description']}}
        </p>
    @endforeach
@endsection