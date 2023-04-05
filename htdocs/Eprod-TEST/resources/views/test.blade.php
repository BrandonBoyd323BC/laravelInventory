@extends('layout')

@section('content')
    <h1>{{$heading}}</h1>
    <h2>{{$subHeader}}</h2>
    @if(count($items) == 0)
        <p>It's Paulie Walnuts!</p>
    @else
        @foreach($items as $listItem)
            <h2>
                {{$heading}}
            </h2>
            <p>
                {{$listItem['description']}}
            </p>
        @endforeach
    @endif
@endsection