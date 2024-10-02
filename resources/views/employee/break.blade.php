@extends('layouts.admin')

@section('page-title')
    {{ __('Break List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Break List') }}</li>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {!! session('status') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
    <div class="mt-2" id="" style="">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => ['employee.break.index'], 'method' => 'get', 'id' => 'break_filter']) }}
                <div class="d-flex align-items-center justify-content-end">
                    
                    <!-- Week Off Date Field -->
                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                        <div class="btn-box">
                            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                            {{ Form::date('date', request()->get('date'), ['class' => 'form-control', 'placeholder' => 'Date']) }}
                        </div>
                    </div>

                    <!-- Date Range Field -->
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-4">
                        <div class="btn-box">
                            {{ Form::label('date_range', __('Date Range'), ['class' => 'form-label']) }}
                            <div class="d-flex">
                                {{ Form::date('start_date', request()->get('start_date'), ['class' => 'form-control', 'placeholder' => 'Start Date']) }}
                                <span class="mx-2" style="margin-top:7px; font-weight:900; font-size:16px;"><strong>to</strong></span>
                                {{ Form::date('end_date', request()->get('end_date'), ['class' => 'form-control', 'placeholder' => 'End Date']) }}
                            </div>
                        </div>
                    </div>

                    @if (\Auth::user()->type != 'employee')
                    <!-- Employee Field -->
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-4">
                        <div class="btn-box">
                            {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                            {{ Form::select('employee', $usersList, request()->get('employee'), ['class' => 'form-control select', 'id' => 'employee_id']) }}
                        </div>
                    </div>
                    @endif

                    <!-- Buttons -->
                    <div class="col-auto float-end ms-2 mt-4">
                        <a href="#" class="btn btn-sm btn-primary"
                            onclick="document.getElementById('break_filter').submit(); return false;"
                            data-bs-toggle="tooltip" title="Apply">
                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                        </a>
                        <a href="{{ route('employee.break.index') }}" class="btn btn-sm btn-danger"
                            data-bs-toggle="tooltip" title="Reset">
                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee ID') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Total Break') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($breaks as $break)
                                <tr>
                                @php
                                    $employee = App\Models\User::find($break->employee_id);
                                @endphp
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $break->date }}</td>
                                    <td>{{ $break->total_break }}</td>
                                    <td>
                                        <a href="#" data-url="{{ route('break.details', ['date' => $break->date, 'employee_id' => $break->employee_id]) }}" data-ajax-popup="true" data-title="{{ __('View Details') }}"
                                            data-size="lg" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="{{ __('View') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
