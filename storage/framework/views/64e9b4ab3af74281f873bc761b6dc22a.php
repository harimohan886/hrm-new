<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Break List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Break List')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(session('status')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo session('status'); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
    <div class="mt-2" id="" style="">
        <div class="card">
            <div class="card-body">
                <?php echo e(Form::open(['route' => ['employee.break.index'], 'method' => 'get', 'id' => 'break_filter'])); ?>

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
                            onclick="document.getElementById('break_filter').submit(); return false;"
                            data-bs-toggle="tooltip" title="Apply">
                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                        </a>
                        <a href="<?php echo e(route('employee.break.index')); ?>" class="btn btn-sm btn-danger"
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
                                <th><?php echo e(__('Employee ID')); ?></th>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Total Break')); ?></th>
                                <th><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $breaks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $break): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                <?php
                                    $employee = App\Models\User::find($break->employee_id);
                                ?>
                                    <td><?php echo e($employee->name); ?></td>
                                    <td><?php echo e($break->date); ?></td>
                                    <td><?php echo e($break->total_break); ?></td>
                                    <td>
                                        <a href="#" data-url="<?php echo e(route('break.details', ['date' => $break->date, 'employee_id' => $break->employee_id])); ?>" data-ajax-popup="true" data-title="<?php echo e(__('View Details')); ?>"
                                            data-size="lg" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                            data-bs-original-title="<?php echo e(__('View')); ?>">
                                            <i class="ti ti-eye"></i>
                                        </a>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hrm-junglesafari/resources/views/employee/break.blade.php ENDPATH**/ ?>