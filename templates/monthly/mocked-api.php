<?php

//getting from request data
$data = json_decode(file_get_contents('php://input'), true);
if(isset($data['loan']) && isset($data['months'])) {
    if($data['months'] > 0) {
        // loan value divided by selected period
        $price_per_month = number_format((int)$data['loan'] / (int)$data['months'], 2, ".", "");
        // returns computed data
        echo json_encode($price_per_month);
    }
}
