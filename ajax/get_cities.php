<?php
include '../connection.php';

header('Content-Type: application/json');

if (isset($_POST['state_id']) && is_numeric($_POST['state_id'])) {
    $state_id = intval($_POST['state_id']);
    
    $query = "SELECT city_id, city_name FROM city_tbl 
              WHERE state_id = $state_id AND city_status = 1 
              ORDER BY city_name";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $cities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cities[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'cities' => $cities
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching cities'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid state ID'
    ]);
}
?>