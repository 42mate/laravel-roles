@extends('mate/roles::layouts.tables')

@section('title', __('Permisos'))

@section('table-header')
    <td class="fw-bold text-start">{{ __('Permisos')  }}</td>
    @foreach($roles as $role)
        <td class="text-center fw-bold">{{ ucfirst(strtolower($role->name)) }}</td>
    @endforeach
@endsection

@section('table-prefix')
    {{ forms()->form('POST', route(config('roles.routes.permissions.update')))->open() }}
@endsection

@section('table-body')
    @foreach($permissions as $permission)
        <tr>
            <td class="text-start" >{{ ucwords($permission) }}</td>
            @foreach($roles as $role)
                <td class="text-center">
                    {{ forms()->checkbox('permissions[' . $role->id . '][' . $permission . ']', !empty($role_permissions[$role->id][$permission]), true) }}
                </td>
            @endforeach
        </tr>
    @endforeach
@endsection

@section('table-suffix')
    <div class="mt-3">
        {{ forms()->submit(__('Guardar')) }}
    </div>
    {{ forms()->end() }}
@endsection
