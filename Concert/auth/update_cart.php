<?php
require_once 'session_config.php';
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['UserID'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    // Cari cart aktif
    $stmt = $pdo->prepare("SELECT CartID FROM carts WHERE UserID = ? ORDER BY CreatedDate DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        echo json_encode(['success' => false, 'message' => 'Cart not found']);
        exit;
    }
    
    $cartId = $cart['CartID'];
    
    if ($data['action'] === 'update') {
        if (!isset($data['cart_item_id']) || !isset($data['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE cartitems SET Quantity = ? WHERE CartItemID = ? AND CartID = ?");
        $stmt->execute([$data['quantity'], $data['cart_item_id'], $cartId]);
        
        // Cek apakah ada baris yang terpengaruh
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found or no change']);
        }
    } 
    elseif ($data['action'] === 'delete') {
        if (!isset($data['cart_item_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing cart item ID']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM cartitems WHERE CartItemID = ? AND CartID = ?");
        $stmt->execute([$data['cart_item_id'], $cartId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
        }
    }
    elseif ($data['action'] === 'clear') {
        $stmt = $pdo->prepare("DELETE FROM cartitems WHERE CartID = ?");
        $stmt->execute([$cartId]);
        echo json_encode(['success' => true]);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}