<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Attendance List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Attendance List')); ?></li>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script-page'); ?>
    <script>
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');
    </script>

<script>
    $(document).ready(function() {
            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '<?php echo e(route('monthly.getdepartment')); ?>',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value="0"> <?php echo e(__('All')); ?> </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }
</script>

<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <?php if(session('status')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo session('status'); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(\Auth::user()->type != 'employee'): ?>
    <div class="d-flex align-items-center justify-content-end mb-2" >
        <span id="pdf_error_msg" style="color:red !important; margin-top:25px !important;"></span>
        <span id="pdf_success_msg" style="color:green !important; margin-top:25px !important;"></span>
        <!-- Month Selector -->
        <div class="mx-2">
            <div class="btn-box">
                <?php echo e(Form::label('pdf_month', __('Month'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::month('pdf_month', request()->get('pdf_month', ''), ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off', 'placeholder' => 'Select month', 'id' => 'pdf_month'])); ?>

            </div>
        </div>

        <!-- Download Button -->
        <div class="mx-2" style="margin-top:30px !important;">
            <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="<?php echo e(__('Download')); ?>">
                <span class="btn-inner--icon" id="setDownloadStatus">Download ⬇️</span>
            </a>
        </div>
    </div>
    <?php endif; ?>


    <!-- <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    <?php echo e(Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter'])); ?>

                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">

                                <div class="col-3">
                                    <label class="form-label"><?php echo e(__('Type')); ?></label> <br>

                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="monthly" value="monthly" name="type"
                                            class="form-check-input"
                                            <?php echo e(isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked'); ?>>
                                        <label class="form-check-label" for="monthly"><?php echo e(__('Monthly')); ?></label>
                                    </div>
                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="daily" value="daily" name="type"
                                            class="form-check-input"
                                            <?php echo e(isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="daily"><?php echo e(__('Daily')); ?></label>
                                    </div>

                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('month', __('Month'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn'])); ?>

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('date', __('Date'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control month-btn'])); ?>

                                    </div>
                                </div>
                                <?php if(\Auth::user()->type != 'employee'): ?>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('branch', __('Branch'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select', 'id' => 'branch_id'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        

                                        <div class="form-icon-user" id="department_div">
                                            <?php echo e(Form::label('department', __('Department'), ['class' => 'form-label'])); ?>

                                            <select class="form-control select department_id" name="department_id"
                                                id="department_id" placeholder="Select Department">
                                            </select>
                                        </div>

                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">

                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('attendanceemployee_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Apply')); ?>"
                                        data-original-title="<?php echo e(__('apply')); ?>">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>

                                    <a href="<?php echo e(route('attendanceemployee.index')); ?>" class="btn btn-sm btn-danger "
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>"
                                        data-original-title="<?php echo e(__('Reset')); ?>">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                    </a> -->

                                    <!-- <a href="#" data-url="<?php echo e(route('attendance.file.import')); ?>"
                                        data-ajax-popup="true" data-title="<?php echo e(__('Import  Attendance CSV File')); ?>"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-bs-original-title="<?php echo e(__('Import')); ?>">
                                        <i class="ti ti-file"></i>
                                    </a> -->

                                <!-- </div>

                            </div>
                        </div>
                    </div>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div> -->


<!-- For Manually Punch form start  -->

<?php if(\Auth::user()->type=='company' || \Auth::user()->type=='hr'): ?>
<div class="col-sm-12">
    <div class="mt-2" id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
                <h4>Manually Employees Attendance:</h4>
                <?php echo e(Form::open(['route' => ['manual.employees.attendance.store'], 'method' => 'post', 'id' => 'manual_employee_attendance'])); ?>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="row">

                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="form-group">
                                <?php echo e(Form::label('user', __('Employees'), ['class' => 'form-label'])); ?>

                                <div class="form-icon-user">
                                    <?php echo e(Form::select('user_id[]', $employee_option, null, ['class' => 'form-control select2', 'id' => 'choices-multiple', 'multiple' => 'multiple', 'required' => 'required'])); ?>

                                </div>
                            </div>
                        </div>


                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('from_date', 'From Date', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::date('from_date', '', ['class' => 'form-control', 'placeholder' => 'From Date'])); ?>

                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('to_date', 'To Date', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::date('to_date', '', ['class' => 'form-control', 'placeholder' => 'To Date'])); ?>

                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('clock_in', 'Clock In', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('clock_in', '', ['class' => 'form-control', 'placeholder' => 'HH:mm:ss'])); ?>

                                </div>
                                <span style="color:red; font-size:11px; margin-top:10px;"><b>Note:- Clock In Time Example: HH:mm:ss (00:00:00) & 24 Hours Time Format (10:00:00)</b></span>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('clock_out', 'Clock Out', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('clock_out', '', ['class' => 'form-control', 'placeholder' => 'HH:mm:ss'])); ?>

                                </div>
                                <span style="color:red; font-size:11px; margin-top:10px;"><b>Note:- Clock Out Time Example: HH:mm:ss (00:00:00) & 24 Hours Time Format (19:00:00)</b></span>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row align-items-center justify-content-end mt-3">
                    <div class="col-auto">
                        <div class="row">
                            <div class="col-auto mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('manual_employee_attendance').submit(); return false;"
                                    data-bs-toggle="tooltip" title="Apply">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="<?php echo e(route('attendanceemployee.index')); ?>" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="Reset">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- For Manually Punch form end  -->




<div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
    <div class="mt-2" id="" style="">
        <div class="card">
            <div class="card-body">
                <?php echo e(Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'emp_attendance_filter'])); ?>

                <div class="d-flex align-items-center justify-content-end">
                    
                    <!-- Week Off Date Field -->
                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                        <div class="btn-box">
                            <?php echo e(Form::label('date', __('Date'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::date('date', request()->get('date'), ['class' => 'form-control', 'placeholder' => 'Date'])); ?>

                        </div>
                    </div>

                    <!-- Date Range Field -->
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-4">
                        <div class="btn-box">
                            <?php echo e(Form::label('date_range', __('Date Range'), ['class' => 'form-label'])); ?>

                            <div class="d-flex">
                                <?php echo e(Form::date('start_date', request()->get('start_date'), ['class' => 'form-control', 'placeholder' => 'Start Date'])); ?>

                                <span class="mx-2" style="margin-top:7px; font-weight:900; font-size:16px;"><strong>to</strong></span>
                                <?php echo e(Form::date('end_date', request()->get('end_date'), ['class' => 'form-control', 'placeholder' => 'End Date'])); ?>

                            </div>
                        </div>
                    </div>

                    <?php if(\Auth::user()->type != 'employee'): ?>
                    <!-- Employee Field -->
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-4">
                        <div class="btn-box">
                            <?php echo e(Form::label('employee', __('Employee'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::select('employee', $usersList, request()->get('employee'), ['class' => 'form-control select', 'id' => 'employee_id'])); ?>

                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Buttons -->
                    <div class="col-auto float-end ms-2 mt-4">
                        <a href="#" class="btn btn-sm btn-primary"
                            onclick="document.getElementById('emp_attendance_filter').submit(); return false;"
                            data-bs-toggle="tooltip" title="Apply">
                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                        </a>
                        <a href="<?php echo e(route('attendanceemployee.index')); ?>" class="btn btn-sm btn-danger"
                            data-bs-toggle="tooltip" title="Reset">
                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                        </a>
                    </div>
                </div>
                <?php echo e(Form::close()); ?>

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
                                <?php if(\Auth::user()->type != 'employee'): ?>
                                    <th><?php echo e(__('Employee')); ?></th>
                                <?php endif; ?>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Clock In')); ?></th>
                                <th><?php echo e(__('Clock Out')); ?></th>
                                <th><?php echo e(__('Late')); ?></th>
                                <th><?php echo e(__('Early Leaving')); ?></th>
                                <th><?php echo e(__('Overtime')); ?></th>
                                <?php if(Gate::check('Edit Attendance') || Gate::check('Delete Attendance')): ?>
                                    <th width="200px"><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>

                            <?php $__currentLoopData = $attendanceEmployee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <td><?php echo e(!empty($attendance->employee) ? $attendance->employee->name : ''); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo e(\Auth::user()->dateFormat($attendance->date)); ?></td>
                                    <td><?php echo e($attendance->status); ?></td>
                                    <td><?php echo e($attendance->clock_in); ?></td>
                                    <td><?php echo e($attendance->clock_out); ?></td>
                                    <?php if($attendance->late > '00:00:00'): ?>
                                        <td style="color:red;"><b><?php echo e($attendance->late); ?></b></td>
                                    <?php else: ?>
                                        <td>00:00:00</td>                                      
                                    <?php endif; ?>

                                    <?php if($attendance->early_leaving > '00:00:00'): ?>
                                        <td style="color:red;"><b><?php echo e($attendance->early_leaving); ?></b></td>
                                    <?php else: ?>
                                        <td><?php echo e($attendance->early_leaving); ?></td>
                                    <?php endif; ?>

                                    <?php if($attendance->employee->enable_ot=="Enabled"): ?>
                                    
                                        <?php if($attendance->overtime > '00:00:00'): ?>
                                            <td style="color:green;"><b><?php echo e($attendance->overtime); ?></b></td>
                                        <?php else: ?>
                                            <td><?php echo e($attendance->overtime); ?></td>
                                        <?php endif; ?> 
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif; ?>
                                    <td class="Action">
                                        <?php if(Gate::check('Edit Attendance') || Gate::check('Delete Attendance')): ?>
                                            <span>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Attendance')): ?>
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="<?php echo e(URL::to('attendanceemployee/' . $attendance->id . '/edit')); ?>"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="<?php echo e(__('Edit Attendance')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Attendance')): ?>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['attendanceemployee.destroy', $attendance->id],
                                                            'id' => 'delete-form-' . $attendance->id,
                                                        ]); ?>

                                                        <a href="#"
                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"><i
                                                                class="ti ti-trash text-white text-white"></i></a>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
<script>
    $(document).ready(function() {
        var now = new Date();
        
        // Calculate previous month
        var prevMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        
        // Extract year and month
        var year = prevMonth.getFullYear();
        var month = prevMonth.getMonth() + 1; // Months are zero-based in JavaScript
        
        // Format month as two digits
        if (month < 10) month = "0" + month;
        
        // Construct date string in YYYY-MM format
        var previousMonth = year + '-' + month;
        
        // Set the value of the input with class 'current_date'
        $('.current_date').val(previousMonth);
    });

    function saveAsPDF(){
    console.log("Checking");

    $("#setDownloadStatus").text("Wait.. ⏳🥱");

    var pdfMonth = $("#pdf_month").val();
    var year = new Date().getFullYear();

    console.log("Month:", pdfMonth);
    console.log("Year:", year);

    $.ajax({
        url: '/generate-attendance-pdf',
        type: 'GET',
        data: {
            _token: '<?php echo e(csrf_token()); ?>',
            month: pdfMonth,
            year: year
        },
        success: function(response) {
            console.log(response);

            if (response.error_msg) {
                // Display the error message
                $("#pdf_error_msg").text(response.error_msg);
                $("#pdf_success_msg").text(''); // Clear success message
            } else {
                // Create a link to download the PDF
                var link = document.createElement('a');
                link.href = response.pdfUrl; // URL of the generated PDF
                link.download = 'Monthly-Attendance.pdf'; // File name to save
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                $("#setDownloadStatus").text("Download ⬇️");

                // Display the success message
                $("#pdf_success_msg").text(response.success_msg);
                $("#pdf_error_msg").text(''); // Clear error message
            }
        },
        error: function(xhr) {
            console.error("An error occurred while generating the PDF.");
            $("#pdf_error_msg").text('An error occurred. Please try again.');
            $("#pdf_success_msg").text(''); // Clear success message
        }
    });
}


</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hrm-junglesafari/resources/views/attendance/index.blade.php ENDPATH**/ ?>