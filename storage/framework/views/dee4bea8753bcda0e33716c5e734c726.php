<?php echo e(Form::open(['url' => 'wfh/changeaction', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th><?php echo e(__('Employee')); ?></th>
                    <td><?php echo e(!empty($wfh->employee_id) ? $wfh->employees->name : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Start Date ')); ?></th>
                    <td><?php echo e(!empty($wfh->start_date) ? $wfh->start_date : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('End Date ')); ?></th>
                    <td><?php echo e(!empty($wfh->end_date) ? $wfh->end_date : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Created At')); ?></th>
                    <td><?php echo e($wfh->created_at); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Updated At')); ?></th>
                    <td><?php echo e($wfh->updated_at); ?></td>
                </tr>               
                <tr>
                    <th><?php echo e(__('Remark')); ?></th>
                    <td><?php echo e(!empty($wfh->remark) ? $wfh->remark : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Status')); ?></th>
                    <td><?php echo e(!empty($wfh->status) ? $wfh->status : ''); ?></td>
                </tr>
                <input type="hidden" value="<?php echo e($wfh->id); ?>" name="wfh_id">
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

<?php /**PATH /var/www/hrm-junglesafari/resources/views/wfh/action.blade.php ENDPATH**/ ?>