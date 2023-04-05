@extends('layout')

@section('content')   
    <h1>{{$heading}}</h1>

    @if(count($testers) == 0)
        <p>Nothing Here Test!</p>
        <p>{{$answer}}</p>
    @else
        @foreach($testers as $testItem)
            <h2>
                {{$heading}}
            </h2>
            <p>{{$testItem['description']}}</p>
        @endforeach
    @endif
@endsection