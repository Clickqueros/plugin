<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align:center; }
        h1 { font-size:32px; }
        p { font-size:20px; }
    </style>
</head>
<body>
    <h1>Certificado</h1>
    <p>Otorgado a <strong><?php echo esc_html( $first . ' ' . $last ); ?></strong></p>
    <p>Por su participación como <strong><?php echo esc_html( $position ); ?></strong></p>
    <p>En el curso <strong><?php echo esc_html( $course ); ?></strong></p>
    <p>Código: <strong><?php echo esc_html( $code ); ?></strong></p>
</body>
</html>
