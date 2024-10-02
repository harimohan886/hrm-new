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
                <?php $__currentLoopData = $breaks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $break): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <?php
                            $employee = App\Models\User::find($break->employee_id);
                            
                        ?>
                        <td><?php echo e($employee->name); ?></td> 
                        <td><?php echo e($break->date); ?></td>
                        <td><?php echo e($break->start_break); ?></td>
                        <td><?php echo e($break->end_break); ?></td>
                        <td><?php echo e($break->total_break); ?></td>
                        <td><?php echo e($break->created_at); ?></td>
                        <td><?php echo e($break->updated_at); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH /var/www/html/hrm-system/resources/views/employee/view-break.blade.php ENDPATH**/ ?>