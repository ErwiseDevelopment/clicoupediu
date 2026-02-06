<!DOCTYPE html>
<html lang="pt-br" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Sistema Delivery'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGH7ygV2EqebroAxOpfIElfgh1MIEVg-U&libraries=places"></script>

<link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/favicon.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-title" content="ClicouPediu Admin">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Ajuste para o Cropper não estourar o modal */
        .img-container img { max-width: 100%; }
    </style>
</head>
</head>
<body class="h-full flex flex-col">