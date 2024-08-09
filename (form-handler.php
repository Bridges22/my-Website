<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $terms = $data['terms'];

    if ($terms) {
        // Save data to the database or file
        echo json_encode(['message' => 'Entry received!']);
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'You must agree to the terms and conditions.']);
    }
}
?>
