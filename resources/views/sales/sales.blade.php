@extends('sales.layout')

@section('sales-title')
    {{ $sales->title }}
@endsection

@if ($sales->has_image)
    @section('meta-img')
        {{ $sales->imageUrl }}
    @endsection
@endif

@section('sales-content')
    {!! breadcrumbs(['Site Sales' => 'sales', $sales->title => $sales->url]) !!}
    @include('sales._sales', ['sales' => $sales, 'page' => true])

    <hr class="mb-5" />
    @if ((isset($sales->comments_open_at) && $sales->comments_open_at < Carbon\Carbon::now()) || (Auth::check() && (Auth::user()->hasPower('manage_sales') || Auth::user()->hasPower('comment_on_sales'))) || !isset($sales->comments_open_at))
        @comments(['model' => $sales, 'perPage' => 5])
    @else
        <div class="alert alert-warning text-center">
            <p>Comments for this sale aren't open yet! They will open {!! pretty_date($sales->comments_open_at) !!}.</p>
        </div>
    @endif
@endsection
