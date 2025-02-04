<?php
session_start();
require_once '../db/database.php';
require_once '../functions/auth_functions.php';
require_once '../functions/item_functions.php';

// Check if user is logged in and has the correct role (superadmin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $unit = trim($_POST['unit']);

        // Detailed logging for debugging
        error_log("Add Item Request - Category ID: $categoryId, Name: $name, Description: $description, Quantity: $quantity, Unit: $unit");

        // Validate required fields
        if (empty($categoryId)) {
            throw new Exception('Category is required');
        }
        if (empty($name)) {
            throw new Exception('Item name is required');
        }
        if ($quantity < 0) {
            throw new Exception('Quantity cannot be negative');
        }
        if (empty($unit)) {
            throw new Exception('Unit of measurement is required');
        }

        // Add the item
        if (addItem($conn, $categoryId, $name, $description, $quantity, $unit)) {
            echo json_encode([
                'success' => true,
                'message' => 'Item added successfully'
            ]);
        } else {
            error_log("Failed to add item - Detailed error not available");
            throw new Exception('Failed to add item');
        }
    } catch (Exception $e) {
        error_log("Add Item Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}