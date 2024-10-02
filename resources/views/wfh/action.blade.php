{{ Form::open(['url' => 'wfh/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($wfh->employee_id) ? $wfh->employees->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date ') }}</th>
                    <td>{{ !empty($wfh->start_date) ? $wfh->start_date : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date ') }}</th>
                    <td>{{ !empty($wfh->end_date) ? $wfh->end_date : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Created At') }}</th>
                    <td>{{ $wfh->created_at }}</td>
                </tr>
                <tr>
                    <th>{{ __('Updated At') }}</th>
                    <td>{{ $wfh->updated_at }}</td>
                </tr>               
                <tr>
                    <th>{{ __('Remark') }}</th>
                    <td>{{ !empty($wfh->remark) ? $wfh->remark : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>{{ !empty($wfh->status) ? $wfh->status : '' }}</td>
                </tr>
                <input type="hidden" value="{{ $wfh->id }}" name="wfh_id">
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
