<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .id-card {
    margin-top: 30px;
    width: 325px;
    height: 473px;
    /* background: url('/assets/images/card_back2.jpg'); */
    border-radius: 15px;
    box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.3); /* Only bottom and right shadow */
    padding: 20px;
    color: #070707;
    position: relative;
    background-image: url("<?php echo e(asset('/assets/images/lightcolor.jpg')); ?>");
    background-size: cover;
    background-position: center;
}
        .id-card-back {
            margin-top: 30px;
            width: 325px;
            height: 473px;
            /* background: url('/assets/images/card_back2.jpg'); */
            border-radius: 15px;
            box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.3); /* Only bottom and right shadow */
            padding: 20px;
            color: #070707;
            position: relative;
            background-image: url("<?php echo e(asset('/assets/images/idcardback.jpg')); ?>"); 
            background-size: cover; 
            background-position: center;
            
        }

        .photo {
            text-align: center;
            margin-bottom: 20px;
        }
        .photo img {
            border-radius: 50%;
    width: 131px;
    height: 131px;
    border: 3px solid #fff;
    margin-top: 69px;
        }
        .designation{
            color: #4ea501; 
            margin-top: -11px;
            font-weight: 600;
            text-align: center;
            font-size:12px;
        }
        .employee_name{
            font-weight: 600;
            text-align: center;
            margin-top: -12px;
        }
        .qrimage{
            height: 50px;
    width: 50px;
    position: relative;
    left: 106px;
    top: -5px;
}

.qrimage2{
            height: 50px;
    width: 50px;
    position: relative;
    left: 106px;
    top: 20px;
}

.barimage{
    height: 75px;
    width: 258px;
    position: relative;
    left: -93px;
    top: 3px;
}

        .footer{
            font-size: 13px;
            margin-top: 4px;
        }
        .footerback {
    font-size: 13px;
    margin-top: 100px;
}
        .address{

    font-size: 13px;
    color: #4ea501;
    position: relative;
    font-weight: 600;
    top: 34px;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="id-card">
                    <div class="photo" style="">
                        <img src="https://hrm.junglesafariindia.in/storage/uploads/avatar/<?php echo e($data['photo']); ?>" alt="Photo" >
                    </div>
                    <div class="info">
                        <div class="name">
                            <h5 class="employee_name"><?php echo e($data['name']); ?></h5>
                            <p class="designation"><?php echo e($data['designation']); ?></p>
                        </div>
                        <div>
                            <ul type="none">
                                <li style="font-size: 15px;"><strong>ID NO</strong><span style="margin-left: 5px;">: <?php echo e($data['id_no']); ?></span></li>
                                <li style="font-size: 15px;"><strong>Join Date</strong> <span style="margin-left: 5px;">: <?php echo e($data['join_date']); ?></span></li>
                                <li style="font-size: 15px;"><strong>Phone</strong><span style="margin-left: 5px;">: <?php echo e($data['phone']); ?></span></li>
                                <li style="font-size: 13px;"><strong>Email</strong><span style="margin-left: 5px;">: <?php echo e($data['email']); ?></span></li>
                                <!-- <li style="font-size: 13px;"><strong>Address</strong><span style="margin-left: 3px;">: village-multanpur,Tundla,Agra</span></li> -->
                                
                            </ul>

                        </div>

                        <div class="qrimage">
                            <div class="qrimage">
                                <?php echo QrCode::size(40)->generate("EMP NAME: " . $data['name'] . ", EMP ID: " . $data['id_no']); ?>

                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        © 2024 Jungle Safari India. All rights reserved.
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="id-card-back ">
                    <div class="row" style="margin-top: 43px;">
                        <div class="col-2" style="font-size: 12px; margin-right: 0px;">
                            <b>Email</b>
                        </div>
                        <div class="col-10" style="font-size: 12px; margin-right: 0px;">
                            : contact@junglesafariindia.in
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2" style="font-size: 12px; margin-right: 0px;">
                            <b>Phone</b>
                        </div>
                        <div class="col-10" style="font-size: 12px; margin-right: 0px;">
                            : 9971717045
                        </div>
                    </div>

                    <div class="p-1 my-2" style="font-size: 12px; border: 1px solid #f0eaea; border-radius: 5px;">
                        <p style="margin-bottom: -2px;"><b>1.</b> Immediately report to HR if the ID card is stolen or lost. </p>
                        <p style="margin-bottom: -2px;"><b>2.</b> This card is property of Jungle Safari India and must be returned upon request.</p>
                        <p style="margin-bottom: -2px;"><b>3.</b> This ID card must be displayed at all times while you're inside the company premises</p>
                        <p style="margin-bottom: -2px;"><b>4.</b> Use this card to punch in and out at entry points.</p>
                    </div>
                    
                    <p class="address" >Jungle Safari India,A-2 Second Floor, Ganesh Nagar, Pandav Nagar Complex Delhi 110092</p>

                    <div class="qrimage2">
                            <div class="qrimage2">
                                <?php echo QrCode::size(70)->generate('https://junglesafariindia.in/'); ?>

                            </div>
                    </div>
    



                    <div class="footerback">
                        <p class="text-center">© 2024 Jungle Safari India. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html><?php /**PATH /var/www/hrm-junglesafari/resources/views/id_card.blade.php ENDPATH**/ ?>