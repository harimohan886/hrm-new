
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Wfh') }}
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Wfh ') }}</li>

    @if (\Auth::user()->type == 'employee')
        
        <style>
            .add-button-container {
                position: absolute;
                top: 40px;
                right: 30px;
                z-index: 1000; /* Ensure it's on top of other elements */
            }
        </style>

        <div class="add-button-container">
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editwfhModal">+</button>
        </div>

        <div class="modal fade" id="editwfhModal" tabindex="-1" aria-labelledby="editwfhModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editwfhModal">Create Shift</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('wfh.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <!-- Shift Code -->
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="" required>
                            </div>
                            <!-- Shift Name -->
                            <div class="form-group">
                                <label for="remark">Remark</label>
                                <input type="text" class="form-control" id="remark" name="remark" value="" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

@endsection

   


@section('content')

<div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
    <div class="mt-2" id="" style="">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => ['wfh.index'], 'method' => 'get', 'id' => 'wfh_filter']) }}
                <div class="d-flex align-items-center justify-content-end">
                    
                    <!-- Week Off Date Field -->
                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 created_at">
                        <div class="btn-box">
                            {{ Form::label('created_at', __('Creation Date'), ['class' => 'form-label']) }}
                            {{ Form::date('created_at', request()->get('created_at'), ['class' => 'form-control', 'placeholder' => 'Creation Date']) }}
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

                    <!-- Status Field -->
                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mx-2">
                        <div class="btn-box">
                            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                            {{ Form::select('status', [
                                '' => 'Select Status',
                                'Approved' => 'Approved',
                                'Reject' => 'Reject',
                                'Pending' => 'Pending'
                            ], request()->get('status'), ['class' => 'form-control']) }}
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
                            onclick="document.getElementById('wfh_filter').submit(); return false;"
                            data-bs-toggle="tooltip" title="Apply">
                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                        </a>
                        <a href="{{ route('wfh.index') }}" class="btn btn-sm btn-danger"
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
                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Remark') }}</th>
                                <th>{{ __('status') }}</th>
                                <th>{{ __('created_at') }}</th>
                                <th>{{ __('updated_at') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wfhs as $wfh)
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($wfh->employee_id) ? $wfh->employees->name : '' }}
                                        </td>
                                    @endif
                                    <td>{{ \Auth::user()->dateFormat($wfh->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($wfh->end_date) }}</td>
                                    <td>{{ $wfh->remark ?? '' }}</td>
                                   
                                    <td>
                                        @if ($wfh->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ $wfh->status }}</div>
                                        @elseif($wfh->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ $wfh->status }}</div>
                                        @elseif($wfh->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ $wfh->status }}</div>
                                        @endif
                                    </td>

                                    <td>{{ $wfh->created_at ?? '' }}</td>
                                    <td>{{ $wfh->updated_at ?? '' }}</td>

                                    <td class="Action">
                                        <span>

                                            @if (\Auth::user()->type != 'employee')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('wfh/' . $wfh->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Wfh Action') }}"
                                                            data-bs-original-title="{{ __('Wfh Leave') }}">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                        @if (\Auth::user()->type != 'employee')
                                                            <div class="action-btn bg-danger ms-2" style="margin-top: -20px;">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['wfh.destroy', $wfh->id],
                                                                    'id' => 'delete-form-' . $wfh->id,
                                                                ]) !!}
                                                                <a style="padding-top:20px; " href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    
                                                @else
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('wfh/' . $wfh->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Wfh Action') }}"
                                                            data-bs-original-title="{{ __('Manage Wfh') }}">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>


                                                    </div>
                                                @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                        }
                        if (oldval) {
                            if (oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr(
                                    "selected", "selected");
                            }
                        }
                    });

                }
            });
        });
    </script>
@endpush

