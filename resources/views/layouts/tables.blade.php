@extends('layouts.app')

@section('main-content')
    <div class="">
        <div class="navbar">
            <div class="container-fluid">
                <h2>
                    @yield('title')
                </h2>
                <div class="d-flex">
                    @yield('title-actions')
                </div>
            </div>
        </div>

        <div class="messages">
            {{ forms()->errors() }}
            {{ forms()->success() }}
        </div>

        <div class="filter-wrapper">
            @yield('filter')
        </div>

        <div class="table-wrapper table-responsive mb-3">
            @yield('table-prefix')
            <table class="table table-bordered rounded table-hover table-striped">
                <thead class="table-secondary">
                    <tr>
                        @yield('table-header')
                    </tr>
                </thead>
                <tbody>
                    @yield('table-body')

                    @sectionMissing('table-body')
                        @foreach ($models as $model)
                            @include(
                            ($modelName ?? Str::snake(class_basename(get_class($model)))) . '._tablerow', ['model' => $model]                            )
                        @endforeach
                    @endif
                </tbody>
            </table>
            @yield('table-suffix')
        </div>

        @if (! empty($models) && $models instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="pagination-wrapper">
                {{ $models->links() }}
            </div>
        @endif
    </div>
@endsection