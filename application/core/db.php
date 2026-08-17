<?php
return new PDO(
    'pgsql:host=localhost;dbname=image-gallery-php',
    'postgres',
    'postgres',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);