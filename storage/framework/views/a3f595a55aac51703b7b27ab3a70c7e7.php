<?php echo e(Form::open(['url' => 'weekoff/changeaction', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th><?php echo e(__('Employee')); ?></th>
                    <td><?php echo e(!empty($weekoff->employee_id) ? $weekoff->employees->name : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Weekoff Date ')); ?></th>
                    <td><?php echo e(!empty($weekoff->week_off_date) ? $weekoff->week_off_date : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Created At')); ?></th>
                    <td><?php echo e($weekoff->created_at); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Updated At')); ?></th>
                    <td><?php echo e($weekoff->updated_at); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Weekoff Day')); ?></th>
                    <td><?php echo e(!empty($weekoff->week_off_date) ? date('l', strtotime($weekoff->week_off_date)) : ''); ?></td>
                </tr>                
                <tr>
                    <th><?php echo e(__('Remark')); ?></th>
                    <td><?php echo e(!empty($weekoff->remark) ? $weekoff->remark : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Status')); ?></th>
                    <td><?php echo e(!empty($weekoff->status) ? $weekoff->status : ''); ?></td>
                </tr>
                <input type="hidden" value="<?php echo e($weekoff->id); ?>" name="weekoff_id">
            </table>
        </div>
    </div>
</div>

<?php if(Auth::user()->type == 'company' || Auth::user()->type == 'hr'): ?>
<div class="modal-footer">
    <input type="submit" value="<?php echo e(__('Approved')); ?>" class="btn btn-success rounded" name="status">
    <input type="submit" value="<?php echo e(__('Reject')); ?>" class="btn btn-danger rounded" name="status">
</div>
<?php endif; ?>


<?php echo e(Form::close()); ?>

<?php /**PATH /var/www/hrm-junglesafari/resources/views/weekoff/action.blade.php ENDPATH**/ ?>