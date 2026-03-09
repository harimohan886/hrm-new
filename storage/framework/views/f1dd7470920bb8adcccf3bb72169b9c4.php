<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Monthly Attendance')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Manage Monthly Attendance Report')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-button'); ?>
    <div class="d-flex align-items-center justify-content-end">
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    

    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    <?php echo e(Form::open(['route' => ['report.monthly.attendance'], 'method' => 'get', 'id' => 'report_monthly_attendance'])); ?>

                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('month', __(' Month'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::month('month', isset($_GET['month']) ? $_GET['month'] : '', ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off', 'placeholder' => 'Select month'])); ?>

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <?php echo e(Form::label('branch', __('Branch'), ['class' => 'form-label'])); ?>

                                        <?php echo e(Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select', 'required' => 'required'])); ?>

                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        <div class="btn-box" id="department_id">
                                            <?php echo e(Form::label('department', __('Department'), ['class' => 'form-label'])); ?>

                                            <select class="form-control select department_id" name="department"
                                                id="department_id" placeholder="Select Department" required>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box" id="employee_div">
                                        <?php echo e(Form::label('employee', __('Employee'), ['class' => 'form-label'])); ?>

                                        <select class="form-control select" name="employee_id[]" id="employee_id"
                                            placeholder="Select Employee">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto mt-4">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('report_monthly_attendance').submit(); return false;"
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Apply')); ?>"
                                        data-original-title="<?php echo e(__('apply')); ?>">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="<?php echo e(route('report.monthly.attendance')); ?>" class="btn btn-sm btn-danger "
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>"
                                        data-original-title="<?php echo e(__('Reset')); ?>">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
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

    <!-- <div id="printableArea">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-report"></i>
                                </div>
                                <div class="ms-3">
                                    <input type="hidden"
                                        value="<?php echo e($data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . 'Department'); ?>"
                                        id="filename">
                                    <h5 class="mb-0"><?php echo e(__('Report')); ?></h5>
                                    <div>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Attendance Summary')); ?></p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if($data['branch'] != 'All'): ?>
                <div class="col">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-secondary">
                                        <i class="ti ti-sitemap"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0"><?php echo e(__('Branch')); ?></h5>
                                        <p class="text-muted text-sm mb-0">
                                            <?php echo e($data['branch']); ?> </p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($data['department'] != 'All'): ?>
                <div class="col">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-template"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0"><?php echo e(__('Department')); ?></h5>
                                        <p class="text-muted text-sm mb-0"><?php echo e($data['department']); ?></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-secondary">
                                    <i class="ti ti-sum"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?php echo e(__('Duration')); ?></h5>
                                    <p class="text-muted text-sm mb-0"><?php echo e($data['curMonth']); ?>

                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-file-report"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?php echo e(__('Attendance')); ?></h5>
                                    <div>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Total present')); ?>:
                                            <?php echo e($data['totalPresent']); ?></p>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Total leave')); ?>:
                                            <?php echo e($data['totalLeave']); ?></p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-secondary">
                                    <i class="ti ti-clock"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?php echo e(__('Overtime')); ?></h5>
                                    <p class="text-muted text-sm mb-0">
                                        <?php echo e(__('Total overtime in hours')); ?> :
                                        <?php echo e(number_format($data['totalOvertime'], 2)); ?></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-info-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?php echo e(__('Early leave')); ?></h5>
                                    <p class="text-muted text-sm mb-0"><?php echo e(__('Total early leave in hours')); ?>:
                                        <?php echo e(number_format($data['totalEarlyLeave'], 2)); ?></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-secondary">
                                    <i class="ti ti-alarm"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?php echo e(__('Employee late')); ?></h5>
                                    <p class="text-muted text-sm mb-0"><?php echo e(__('Total late in hours')); ?> :
                                        <?php echo e(number_format($data['totalLate'], 2)); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="col">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive py-4 attendance-table-responsive">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th class="active"><?php echo e(__('Name')); ?></th>
                                <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($date); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $employeesAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($attendance['name']); ?></td>
                                    <?php $__currentLoopData = $attendance['status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td>
                                            <?php if($status == 'P'): ?>
                                                <i class="badge p-2 rounded" style="background-color:green;"><?php echo e(__('P')); ?></i>
                                            <?php elseif($status == 'A'): ?>
                                                <i class="badge p-2 rounded" style="background-color:red;"><?php echo e(__('A')); ?></i>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script>
    // function saveAsPDF(){
    //     console.log("Checking");

    //     $("#setDownloadStatus").text("Wait.. ⏳🥱");

    //     var pdfMonth = $("#pdf_month").val();
    //     var year = new Date().getFullYear();

    //     console.log("Month:", pdfMonth);
    //     console.log("Year:", year);

    //     $.ajax({
    //         url: '/generate-attendance-pdf',
    //         type: 'GET',
    //         data: {
    //             _token: '<?php echo e(csrf_token()); ?>',
    //             month: pdfMonth,
    //             year: year
    //         },
    //         success: function(response) {
    //             console.log(response);

    //             if(response.error_msg=="error"){
    //                 $("#pdf_error_msg").text('You cannot generate a PDF for future months.');
    //             }else{
    //             // Create a link to download the PDF
    //             var link = document.createElement('a');
    //             link.href = response.pdfUrl; // URL of the generated PDF
    //             link.download = 'Monthly-Attendance.pdf'; // File name to save
    //             document.body.appendChild(link);
    //             link.click();
    //             document.body.removeChild(link);

    //             $("#setDownloadStatus").text("Download ⬇️");

    //             $("#pdf_success_msg").text('You cannot generate a PDF for future months.');

    //             }
                
    //             // Optionally, show a success message
    //             // alert(response.message);
    //         },
    //         error: function(xhr) {
    //             console.error("An error occurred while generating the PDF.");
    //             alert('An error occurred. Please try again.');
    //         }
    //     });
    // }
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
<?php $__env->startPush('script-page'); ?>
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
                    var emp_selct = `<select class="department_id form-control multi-select" id="choices-multiple" multiple="" required="required" name="department_id[]">
                </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> <?php echo e(__('Select Department')); ?> </option>');
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

        $(document).on('change', '.department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {

            $.ajax({
                url: '<?php echo e(route('monthly.getemployee')); ?>',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {

                    $('#employee_id').empty();

                    $("#employee_div").html('');
                    // $('#employee_div').append('<select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>');
                    $('#employee_div').append(
                        '<label for="employee" class="form-label"><?php echo e(__('Employee')); ?></label><select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>'
                    );

                    $('#employee_id').append('<option value=""><?php echo e(__('Select Employee')); ?></option>');
                    $('#employee_id').append('<option value=""> <?php echo e(__('Select Employee')); ?> </option>');

                    $.each(data, function(key, value) {
                        $('#employee_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    var multipleCancelButton = new Choices('#employee_id', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>

    <!-- <script>
        $(document).ready(function() {
            var now = new Date();
            var month = (now.getMonth() + 1);
            if (month < 10) month = "0" + month;
            var today = now.getFullYear() + '-' + month;
            $('.current_date').val(today);
        });
    </script> -->
<?php $__env->stopPush(); ?>

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
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hrm-junglesafari/resources/views/report/monthlyAttendance.blade.php ENDPATH**/ ?>