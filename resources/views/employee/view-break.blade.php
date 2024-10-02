<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Date</th>
                    <th>Start Break</th>
                    <th>End Break</th>
                    <th>Total Break</th>
                    <th>Created At</th>
                    <th>Updated At</th>
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
                        <td>{{ $break->start_break }}</td>
                        <td>{{ $break->end_break }}</td>
                        <td>{{ $break->total_break }}</td>
                        <td>{{ $break->created_at }}</td>
                        <td>{{ $break->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
