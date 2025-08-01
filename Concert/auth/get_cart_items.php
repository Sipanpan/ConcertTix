<?php
require_once 'session_config.php';
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['UserID'];

try {
    // Dapatkan CartID aktif
    $stmt = $pdo->prepare("SELECT CartID FROM carts WHERE UserID = ? ORDER BY CreatedDate DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();

    if (!$cart) {
        echo json_encode(['success' => true, 'items' => []]);
        exit;
    }

    $cartId = $cart['CartID'];

    // Query untuk mendapatkan item cart dengan informasi lengkap
    $query = "
        SELECT 
            ci.CartItemID,
            ci.Quantity,
            tt.TicketTypeID,
            tt.TypeName AS TicketType,
            tt.Price,
            tt.Description,
            c.ConcertID,
            c.Title AS ConcertTitle,
            c.ConcertDate,
            c.Venue,
            c.ImageURL
        FROM cartitems ci
        JOIN tickettypes tt ON ci.TicketTypeID = tt.TicketTypeID
        JOIN concerts c ON tt.ConcertID = c.ConcertID
        WHERE ci.CartID = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $items]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}