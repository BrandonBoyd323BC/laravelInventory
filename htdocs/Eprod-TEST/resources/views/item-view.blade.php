@extends('layout')

@section('content')   
    <h1>
        <a href="/item-view/{{$list['id']}}">{{$heading}}</a>
    </h1>
    <button>
        <a href="/">Home</a>
    </button>
@endsection
