{{ Form::open(['url' => 'weekoff/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Weekoff Date ') }}</th>
                    <td>{{ !empty($weekoff->week_off_date) ? $weekoff->week_off_date : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Created At') }}</th>
                    <td>{{ $weekoff->created_at }}</td>
                </tr>
                <tr>
                    <th>{{ __('Updated At') }}</th>
                    <td>{{ $weekoff->updated_at }}</td>
                </tr>
                <tr>
                    <th>{{ __('Weekoff Day') }}</th>
                    <td>{{ !empty($weekoff->week_off_date) ? date('l', strtotime($weekoff->week_off_date)) : '' }}</td>
                </tr>                
                <tr>
                    <th>{{ __('Remark') }}</th>
                    <td>{{ !empty($weekoff->remark) ? $weekoff->remark : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>{{ !empty($weekoff->status) ? $weekoff->status : '' }}</td>
                </tr>
                <input type="hidden" value="{{ $weekoff->id }}" name="weekoff_id">
            </table>
        </div>
    </div>
</div>

@if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
<div class="modal-footer">
    <input type="submit" value="{{ __('Approved') }}" class="btn btn-success rounded" name="status">
    <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger rounded" name="status">
</div>
@endif


{{ Form::close() }}
