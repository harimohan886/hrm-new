<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Wfh')); ?>

<?php $__env->stopSection(); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Wfh ')); ?></li>

    <?php if(\Auth::user()->type == 'employee'): ?>
        
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
                    <form action="<?php echo e(route('wfh.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
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

    <?php endif; ?>

<?php $__env->stopSection(); ?>

   


<?php $__env->startSection('content'); ?>

<div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
    <div class="mt-2" id="" style="">
        <div class="card">
            <div class="card-body">
                <?php echo e(Form::open(['route' => ['wfh.index'], 'method' => 'get', 'id' => 'wfh_filter'])); ?>

                <div class="d-flex align-items-center justify-content-end">
                    
                    <!-- Week Off Date Field -->
                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 created_at">
                        <div class="btn-box">
                            <?php echo e(Form::label('created_at', __('Creation Date'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::date('created_at', request()->get('created_at'), ['class' => 'form-control', 'placeholder' => 'Creation Date'])); ?>

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

                    <!-- Status Field -->
                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mx-2">
                        <div class="btn-box">
                            <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::select('status', [
                                '' => 'Select Status',
                                'Approved' => 'Approved',
                                'Reject' => 'Reject',
                                'Pending' => 'Pending'
                            ], request()->get('status'), ['class' => 'form-control'])); ?>

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
                            onclick="document.getElementById('wfh_filter').submit(); return false;"
                            data-bs-toggle="tooltip" title="Apply">
                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                        </a>
                        <a href="<?php echo e(route('wfh.index')); ?>" class="btn btn-sm btn-danger"
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
                                <th><?php echo e(__('Start Date')); ?></th>
                                <th><?php echo e(__('End Date')); ?></th>
                                <th><?php echo e(__('Remark')); ?></th>
                                <th><?php echo e(__('status')); ?></th>
                                <th><?php echo e(__('created_at')); ?></th>
                                <th><?php echo e(__('updated_at')); ?></th>
                                <th width="200px"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $wfhs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wfh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <td><?php echo e(!empty($wfh->employee_id) ? $wfh->employees->name : ''); ?>

                                        </td>
                                    <?php endif; ?>
                                    <td><?php echo e(\Auth::user()->dateFormat($wfh->start_date)); ?></td>
                                    <td><?php echo e(\Auth::user()->dateFormat($wfh->end_date)); ?></td>
                                    <td><?php echo e($wfh->remark ?? ''); ?></td>
                                   
                                    <td>
                                        <?php if($wfh->status == 'Pending'): ?>
                                            <div class="badge bg-warning p-2 px-3 rounded"><?php echo e($wfh->status); ?></div>
                                        <?php elseif($wfh->status == 'Approved'): ?>
                                            <div class="badge bg-success p-2 px-3 rounded"><?php echo e($wfh->status); ?></div>
                                        <?php elseif($wfh->status == "Reject"): ?>
                                            <div class="badge bg-danger p-2 px-3 rounded"><?php echo e($wfh->status); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo e($wfh->created_at ?? ''); ?></td>
                                    <td><?php echo e($wfh->updated_at ?? ''); ?></td>

                                    <td class="Action">
                                        <span>

                                            <?php if(\Auth::user()->type != 'employee'): ?>
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="<?php echo e(URL::to('wfh/' . $wfh->id . '/action')); ?>"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="<?php echo e(__('Wfh Action')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Wfh Leave')); ?>">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                                            <div class="action-btn bg-danger ms-2" style="margin-top: -20px;">
                                                                <?php echo Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['wfh.destroy', $wfh->id],
                                                                    'id' => 'delete-form-' . $wfh->id,
                                                                ]); ?>

                                                                <a style="padding-top:20px; " href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    
                                                <?php else: ?>
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="<?php echo e(URL::to('wfh/' . $wfh->id . '/action')); ?>"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="<?php echo e(__('Wfh Action')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Manage Wfh')); ?>">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>


                                                    </div>
                                                <?php endif; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '<?php echo e(route('leave.jsoncount')); ?>',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value=""><?php echo e(__('Select Leave Type')); ?></option>');

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
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/hrm-junglesafari/resources/views/wfh/index.blade.php ENDPATH**/ ?>