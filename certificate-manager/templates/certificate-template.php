<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin:0; padding:0; font-family: DejaVu Sans, sans-serif; }
        #certificate { position:relative; width:100%; height:100%; }
        #certificate img { width:100%; height:auto; display:block; }
        .field { position:absolute; font-size:20px; }
    </style>
</head>
<body>
    <div id="certificate">
        <?php if ( $bg_image ) : ?>
            <img src="<?php echo esc_url( $bg_image ); ?>" alt="Background" />
        <?php endif; ?>
        <div class="field" style="top:<?php echo intval( $name_y ); ?>px; left:<?php echo intval( $name_x ); ?>px;">
            <?php echo esc_html( $first . ' ' . $last ); ?>
        </div>
        <div class="field" style="top:<?php echo intval( $position_y ); ?>px; left:<?php echo intval( $position_x ); ?>px;">
            <?php echo esc_html( $position ); ?>
        </div>
        <div class="field" style="top:<?php echo intval( $course_y ); ?>px; left:<?php echo intval( $course_x ); ?>px;">
            <?php echo esc_html( $course ); ?>
        </div>
        <div class="field" style="top:<?php echo intval( $code_y ); ?>px; left:<?php echo intval( $code_x ); ?>px;">
            <?php echo esc_html( $code ); ?>
        </div>
    </div>
</body>
</html>
