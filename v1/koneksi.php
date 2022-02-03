<?php
$koneksi = mysqli_connect('remotemysql.com', 'WQdsB3N7G8', 'B5ZJvTi0t8', 'WQdsB3N7G8');
$base_url = 'https://vidlearn.rf.gd';
$api_url = 'https://vidlearn-api.herokuapp.com';

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}