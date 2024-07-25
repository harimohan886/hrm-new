
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Weekoff') }}
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Weekoff ') }}</li>
@endsection

@section('action-button')
    {{-- <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a> --}}

    @if (\Auth::user()->type == 'employee')
                                
    @can('Create Weekoff')
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editWeekoffModal">+</button>

        <div class="modal fade" id="editWeekoffModal" tabindex="-1" aria-labelledby="editWeekoffModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editWeekoffModal">Edit Shift</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('weekoff.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <!-- Shift Code -->
                            <div class="form-group">
                                <label for="week_off_date">Week Off Date</label>
                                <input type="date" class="form-control" id="week_off_date" name="week_off_date" value="" required>
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
    @endcan

    @endif

@endsection


@section('content')
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
                                <th>{{ __('Week Off Date') }}</th>
                                <th>{{ __('Week Off Day Name') }}</th>
                                <th>{{ __('Remark') }}</th>
                                <th>{{ __('status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($weekoffs as $weekoff)
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($weekoff->employee_id) ? $weekoff->employees->name : '' }}
                                        </td>
                                    @endif
                                    <td>{{ \Auth::user()->dateFormat($weekoff->week_off_date) }}</td>
                                    <td>{{ $weekoff->day_name ?? '' }}</td>
                                    <td>{{ $weekoff->remark ?? '' }}</td>
                                   
                                    <td>
                                        @if ($weekoff->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ $weekoff->status }}</div>
                                        @elseif($weekoff->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ $weekoff->status }}</div>
                                        @elseif($weekoff->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ $weekoff->status }}</div>
                                        @endif
                                    </td>

                                    <td class="Action">
                                        <span>
                                            {{-- @if (\Auth::user()->type == 'employee')
                                                @if ($leave->status == 'Pending')
                                                    @can('Edit Leave')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Leave') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                @endif
                                            @else
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                        data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Leave Action') }}"
                                                        data-bs-original-title="{{ __('Manage Leave') }}">
                                                        <i class="ti ti-caret-right text-white"></i>
                                                    </a>
                                                </div>
                                                @can('Edit Leave')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                            data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Leave') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                            @endif

                                            @can('Delete Leave')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leave.destroy', $leave->id], 'id' => 'delete-form-' . $leave->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
                                            @endcan --}}

                                            @if (\Auth::user()->type != 'employee')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('weekoff/' . $weekoff->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Weekoff Action') }}"
                                                            data-bs-original-title="{{ __('Weekoff Leave') }}">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                        {{-- <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editWeekoffModal">+</button> --}}
                                                    </div>
                                                    {{-- @can('Edit Weekoff')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('weekoff/' . $weekoff->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Weekoff') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan --}}
                                                    @can('Delete Weekoff')
                                                        @if (\Auth::user()->type != 'employee')
                                                            <div class="action-btn bg-danger ms-2" style="margin-top: -20px;">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['weekoff.destroy', $weekoff->id],
                                                                    'id' => 'delete-form-' . $weekoff->id,
                                                                ]) !!}
                                                                <a style="padding-top:20px; " href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    @endcan
                                                @else
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('weekoff/' . $weekoff->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Weekoff Action') }}"
                                                            data-bs-original-title="{{ __('Manage Weekoff') }}">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                        {{-- <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#actionWeekoffModal">+</button>

                                                        <div class="modal fade" id="actionWeekoffModal" tabindex="-1" aria-labelledby="actionWeekoffModal" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="actionWeekoffModal">Weekoff Action</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div> --}}
                                                                    {{-- <form action="{{ route('weekoff.store') }}" method="POST">
                                                                        @csrf --}}
                                                                        {{-- <div class="modal-body">
                                                                            hhh --}}
                                                                    {{-- </form> --}}
                                                                {{-- </div>
                                                            </div>
                                                        </div> --}}


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

