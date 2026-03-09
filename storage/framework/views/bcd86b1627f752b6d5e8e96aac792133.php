<?php echo e(Form::open(['route' => ['assets.import'], 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

<div class="modal-body">

    <div class="row">
        <div class="col-md-12 mb-6">
            <label for="file" class="form-label">Download sample product CSV file</label>
            <a href="<?php echo e(asset(Storage::url('uploads/sample')) . '/sample-assets.csv'); ?>"
                class="btn btn-sm btn-primary">
                <i class="ti ti-download"></i> <?php echo e(__("Download")); ?>

            </a>
        </div>
       <!--  <div class="col-md-12">
            <label for="file" class="form-label"><?php echo e(__('Select CSV File')); ?></label>
            
            <div class="choose-file form-group">
               
                <label for="file" class="form-label choose-files bg-primary "><i
                    class="ti ti-upload px-1"></i><?php echo e(__('Choose file here')); ?></label>
            <input type="file" name="file" id="file" class="custom-input-file d-none">

            </div>
        </div> -->  

        <div class="choose-files mt-3">
            <label for="file">
                <div class=" bg-primary "> <i
                        class="ti ti-upload px-1"></i><?php echo e(__('Select CSV File')); ?>

                </div>
                <input type="file" class="form-control file"
                    name="file" id="file"
                    data-filename="file">
            </label>
        </div>


    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Upload')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /var/www/hrm-junglesafari/resources/views/assets/import.blade.php ENDPATH**/ ?>