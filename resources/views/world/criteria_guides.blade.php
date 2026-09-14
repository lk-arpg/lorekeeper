@extends('world.layout')

@section('world-title')
    Criteria Guides
@endsection

@section('world-content')
    {!! breadcrumbs(['World' => 'world', 'Criteria Guides' => 'world/criteria-guides']) !!}

    <h1>Criteria Guides</h1>
    <p>
        Here you can find guides for each criterion. These guides will help you understand how to calculate the amount of currency you will receive for each criteria.
    </p>
    @if (count($criterions))
        @foreach ($criterions as $criterion)
            <div class="card">
                <div class="card-body">
                    @include('criteria._guide', [
                        'criterion' => $criterion,
                        'isPage' => false,
                    ])
                </div>
            </div>
        @endforeach
    @else
        <p>No criteria guides have been added yet.</p>
    @endif
@endsection
