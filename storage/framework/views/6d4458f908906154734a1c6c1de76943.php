<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Timesheet')); ?>

<?php $__env->stopSection(); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Timesheet')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="col-sm-12">
    <div class="mt-2" id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
                <h4>Manage Shift:</h4>
                <?php echo e(Form::open(['route' => ['timesheet.shift.manage.store'], 'method' => 'post', 'id' => 'timesheet_shift_manage'])); ?>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="row">
                            <!-- Shift Code -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('shift_code', 'Shift Code', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('shift_code', '', ['class' => 'form-control', 'placeholder' => 'Shift Code'])); ?>

                                </div>
                            </div>

                            <!-- Shift Name -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('shift_name', 'Shift Name', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('shift_name', '', ['class' => 'form-control', 'placeholder' => 'Shift Name'])); ?>

                                </div>
                            </div>

                            <!-- Start Time -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('start_time', 'Start Time', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('start_time', '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>

                            <!-- End Time -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('end_time', 'End Time', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('end_time', '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>

                            <!-- Shift Hours -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('shift_hours', 'Shift Hours', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('shift_hours', '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center justify-content-end mt-3">
                    <div class="col-auto">
                        <div class="row">
                            <div class="col-auto mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('timesheet_shift_manage').submit(); return false;"
                                    data-bs-toggle="tooltip" title="Apply">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="<?php echo e(route('timesheet.index')); ?>" class="btn btn-sm btn-danger"
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


<!-- Edit Shift Modals -->
<?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="editShiftModal<?php echo e($shift->id); ?>" tabindex="-1" aria-labelledby="editShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editShiftModalLabel">Edit Shift</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('timesheet.shift.manage.update', ['id' => $shift->id])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <!-- Shift Code -->
                    <div class="form-group">
                        <label for="edit_shift_code">Shift Code</label>
                        <input type="text" class="form-control" id="edit_shift_code" name="shift_code" value="<?php echo e($shift->shift_code); ?>" required>
                    </div>
                    <!-- Shift Name -->
                    <div class="form-group">
                        <label for="edit_shift_name">Shift Name</label>
                        <input type="text" class="form-control" id="edit_shift_name" name="shift_name" value="<?php echo e($shift->shift_name); ?>" required>
                    </div>
                    <!-- Start Time -->
                    <div class="form-group">
                        <label for="edit_start_time">Start Time</label>
                        <input type="text" class="form-control" id="edit_start_time" name="start_time" value="<?php echo e($shift->start_time); ?>" required>
                    </div>
                    <!-- End Time -->
                    <div class="form-group">
                        <label for="edit_end_time">End Time</label>
                        <input type="text" class="form-control" id="edit_end_time" name="end_time" value="<?php echo e($shift->end_time); ?>" required>
                    </div>
                    <!-- Shift Hours -->
                    <div class="form-group">
                        <label for="edit_shift_hours">Shift Hours</label>
                        <input type="text" class="form-control" id="edit_shift_hours" name="shift_hours" value="<?php echo e($shift->shift_hours); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<!-- Rest of your page content -->
<div class="col-sm-12 mt-4">
    <div class="card">
        <div class="card-body">
            <h4>Manage Shifts:</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Shift Code</th>
                        <th>Shift Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Shift Hours</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($shift->shift_code); ?></td>
                        <td><?php echo e($shift->shift_name); ?></td>
                        <td><?php echo e($shift->start_time); ?></td>
                        <td><?php echo e($shift->end_time); ?></td>
                        <td><?php echo e($shift->shift_hours); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editShiftModal<?php echo e($shift->id); ?>">Edit</button>
                            <form action="<?php echo e(route('timesheet.shift.manage.delete', ['id' => $shift->id])); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<div class="col-sm-12">
    <div class="mt-2" id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
                <h4>Employee's Policy details:-</h4>
                <?php echo e(Form::open(['route' => ['timesheet.store'], 'method' => 'post', 'id' => 'timesheet_filter'])); ?>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="row">
                            <!-- Policy Name -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('policy_name', 'Policy Name', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('policy_name', $timeSheet->policy_name ?? '', ['class' => 'form-control'])); ?>

                                </div>
                            </div>
                            <!-- Permitted Late Arrival -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('permitted_late_arrival', 'Permitted Late Arrival', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('permitted_late_arrival', $timeSheet->permitted_late_arrival ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                            <!-- Permitted Early Departure -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('permitted_early_departure', 'Permitted Early Departure', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('permitted_early_departure', $timeSheet->permitted_early_departure ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                            <!-- Mark as half day if working hours less than -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('mark_half_day_hours', 'Mark as half day if working hours less than', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('mark_half_day_hours', $timeSheet->mark_half_day_hours ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                            <!-- Mark as absent if working hours less than -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('mark_absent_hours', 'Mark as absent if working hours less than', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('mark_absent_hours', $timeSheet->mark_absent_hours ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mt-5">Late Coming Rule:-</h4>
                <div class="row">
                    <div class="col-xl-10">
                        <div class="row">
                            <!-- Late Comings -->
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('late_1', 'Late 1', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('late_1', $timeSheet->late_1 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                                <div class="btn-box">
                                    <?php echo e(Form::label('late_2', 'Late 2', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('late_2', $timeSheet->late_2 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                                <div class="btn-box">
                                    <?php echo e(Form::label('late_3', 'Late 3', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('late_3', $timeSheet->late_3 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                                <div class="btn-box">
                                    <?php echo e(Form::label('late_4', 'Late 4', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('late_4', $timeSheet->late_4 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                            <!-- Deduct day (%) -->
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    <h5 class="form-label">Deduct day (%)</h5>
                                    <?php echo e(Form::select('deduct_percentage_1', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_1 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                                <div class="btn-box mt-4">
                                    <?php echo e(Form::select('deduct_percentage_2', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_2 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                                <div class="btn-box mt-4">
                                    <?php echo e(Form::select('deduct_percentage_3', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_3 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                                <div class="btn-box mt-4">
                                    <?php echo e(Form::select('deduct_percentage_4', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_4 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mt-5">Early Going Rule:-</h4>
                <div class="row">
                    <div class="col-xl-10">
                        <div class="row">
                            <!-- Early Goings -->
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    <?php echo e(Form::label('early_going_1', 'Early 1', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('early_going_1', $timeSheet->early_going_1 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                                <div class="btn-box">
                                    <?php echo e(Form::label('early_going_2', 'Early 2', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('early_going_2', $timeSheet->early_going_2 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                                <div class="btn-box">
                                    <?php echo e(Form::label('early_going_3', 'Early 3', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('early_going_3', $timeSheet->early_going_3 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                                <div class="btn-box">
                                    <?php echo e(Form::label('early_going_4', 'Early 4', ['class' => 'form-label'])); ?>

                                    <?php echo e(Form::text('early_going_4', $timeSheet->early_going_4 ?? '', ['class' => 'form-control', 'placeholder' => 'HH:mm'])); ?>

                                </div>
                            </div>
                            <!-- Deduct day (%) -->
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    <h5 class="form-label">Deduct day (%)</h5>
                                    <?php echo e(Form::select('deduct_percentage_early_going_1', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_early_going_1 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                                <div class="btn-box mt-4">
                                    <?php echo e(Form::select('deduct_percentage_early_going_2', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_early_going_2 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                                <div class="btn-box mt-4">
                                    <?php echo e(Form::select('deduct_percentage_early_going_3', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_early_going_3 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                                <div class="btn-box mt-4">
                                    <?php echo e(Form::select('deduct_percentage_early_going_4', [
                                        '10' => '10%',
                                        '20' => '20%',
                                        '30' => '30%',
                                        '40' => '40%',
                                        '50' => '50%',
                                        '60' => '60%',
                                        '70' => '70%',
                                        '80' => '80%',
                                        '90' => '90%',
                                        '100' => '100%',
                                    ], $timeSheet->deduct_percentage_early_going_4 ?? null, ['class' => 'form-control'])); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center justify-content-end mt-3">
                    <div class="col-auto">
                        <div class="row">
                            <div class="col-auto mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('timesheet_filter').submit(); return false;"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Apply">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="<?php echo e(route('timesheet.index')); ?>" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
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


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hrm-junglesafari/resources/views/timeSheet/index2.blade.php ENDPATH**/ ?>