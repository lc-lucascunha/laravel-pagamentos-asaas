@extends('layouts.app')

@section('content')
    <div class="row pb-0">
        <div class="col-sm-12">
            <header-vue title="{{config('app.name')}}"/>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <client-vue/>
        </div>
        <div class="col-sm-8">
            <payment-vue/>
        </div>
    </div>
@endsection
