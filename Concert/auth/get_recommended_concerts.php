<?php
require_once 'session_config.php';
include 'config.php';

header('Content-Type: application/json');

try {
    // Cek apakah user sudah login
    if (!isset($_SESSION['UserID'])) {
        // Jika belum login, return konser random
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.ConcertID, c.Title, c.Description, c.ConcertDate, c.ConcertTime, 
                   c.Venue, c.City, c.ImageURL, a.Name as ArtistName,
                   MIN(tt.Price) as MinPrice,
                   GROUP_CONCAT(g.genre SEPARATOR ', ') as Genres
            FROM concerts c
            LEFT JOIN artists a ON c.ArtistID = a.ArtistID
            LEFT JOIN tickettypes tt ON c.ConcertID = tt.ConcertID
            LEFT JOIN concertgenres cg ON c.ConcertID = cg.ConcertID
            LEFT JOIN genres g ON cg.GenreID = g.GenreID
            WHERE c.Status = 'upcoming'
            GROUP BY c.ConcertID
            ORDER BY RAND()
            LIMIT 8
        ");
        $stmt->execute();
        $concerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'concerts' => $concerts,
            'message' => 'Random recommendations (not logged in)'
        ]);
        exit();
    }

    $userID = $_SESSION['UserID'];
    
    // Ambil genre favorit user
    $stmt = $pdo->prepare("
        SELECT GenreID FROM usergenres WHERE UserID = ?
    ");
    $stmt->execute([$userID]);
    $userGenres = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($userGenres)) {
        // Jika user belum memilih genre favorit, return konser populer
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.ConcertID, c.Title, c.Description, c.ConcertDate, c.ConcertTime, 
                   c.Venue, c.City, c.ImageURL, a.Name as ArtistName,
                   MIN(tt.Price) as MinPrice,
                   GROUP_CONCAT(g.genre SEPARATOR ', ') as Genres
            FROM concerts c
            LEFT JOIN artists a ON c.ArtistID = a.ArtistID
            LEFT JOIN tickettypes tt ON c.ConcertID = tt.ConcertID
            LEFT JOIN concertgenres cg ON c.ConcertID = cg.ConcertID
            LEFT JOIN genres g ON cg.GenreID = g.GenreID
            WHERE c.Status = 'upcoming'
            GROUP BY c.ConcertID
            ORDER BY c.ConcertDate ASC
            LIMIT 8
        ");
        $stmt->execute();
        $concerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'concerts' => $concerts,
            'message' => 'Popular recommendations (no favorite genres set)'
        ]);
        exit();
    }
    
    // Buat placeholder untuk IN clause
    $placeholders = str_repeat('?,', count($userGenres) - 1) . '?';
    
    // Ambil konser berdasarkan genre favorit user
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.ConcertID, c.Title, c.Description, c.ConcertDate, c.ConcertTime, 
               c.Venue, c.City, c.ImageURL, a.Name as ArtistName,
               MIN(tt.Price) as MinPrice,
               GROUP_CONCAT(g.genre SEPARATOR ', ') as Genres,
               COUNT(DISTINCT cg.GenreID) as GenreMatchCount
        FROM concerts c
        LEFT JOIN artists a ON c.ArtistID = a.ArtistID
        LEFT JOIN tickettypes tt ON c.ConcertID = tt.ConcertID
        LEFT JOIN concertgenres cg ON c.ConcertID = cg.ConcertID
        LEFT JOIN genres g ON cg.GenreID = g.GenreID
        WHERE c.Status = 'upcoming' 
        AND cg.GenreID IN ($placeholders)
        GROUP BY c.ConcertID
        ORDER BY GenreMatchCount DESC, c.ConcertDate ASC
        LIMIT 8
    ");
    
    $stmt->execute($userGenres);
    $concerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Jika tidak ada konser yang cocok dengan genre favorit, ambil konser random
    if (empty($concerts)) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.ConcertID, c.Title, c.Description, c.ConcertDate, c.ConcertTime, 
                   c.Venue, c.City, c.ImageURL, a.Name as ArtistName,
                   MIN(tt.Price) as MinPrice,
                   GROUP_CONCAT(g.genre SEPARATOR ', ') as Genres
            FROM concerts c
            LEFT JOIN artists a ON c.ArtistID = a.ArtistID
            LEFT JOIN tickettypes tt ON c.ConcertID = tt.ConcertID
            LEFT JOIN concertgenres cg ON c.ConcertID = cg.ConcertID
            LEFT JOIN genres g ON cg.GenreID = g.GenreID
            WHERE c.Status = 'upcoming'
            GROUP BY c.ConcertID
            ORDER BY RAND()
            LIMIT 8
        ");
        $stmt->execute();
        $concerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'concerts' => $concerts,
            'message' => 'Random recommendations (no matching genres found)'
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'concerts' => $concerts,
        'message' => 'Personalized recommendations based on favorite genres'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error in get_recommended_concerts.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>

