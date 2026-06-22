<?php 
include 'db_connect.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publisher Book Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #800000;
            --dark-maroon: #600000;
            --accent-gold: #FACC15;
            --bg-light: #f5f5f5;
            --text-main: #333;
            --text-muted: #666;
            --white: #fff;
            --radius: 14px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-main); line-height: 1.6; }

        /* ================= HEADER WITH GOLD SHIMMER ================= */
        header {
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            color: var(--white);
            padding: 2rem 1rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        header::after {
            content: ""; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(250, 204, 21, 0.2) 50%, rgba(255, 255, 255, 0) 100%);
            transform: skewX(-25deg);
            animation: shine 6s infinite ease-in-out;
            pointer-events: none; z-index: 0;
        }
        header h1, header p { position: relative; z-index: 1; }
        header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.2rem; }
        header p { opacity: 0.9; font-size: 0.95rem; }
        @keyframes shine {
            0% { left: -100%; opacity: 0; }
            20% { opacity: 1; }
            50% { left: 200%; opacity: 0; }
            100% { left: 200%; opacity: 0; }
        }

        /* Layout */
        .container { max-width: 1100px; margin: 2rem auto 4rem; padding: 0 1.5rem; }

        /* Back Button */
        .btn-back {
            display: inline-flex; align-items: center;
            background: var(--white); color: var(--text-main);
            padding: 10px 20px; border-radius: var(--radius);
            text-decoration: none; font-weight: 600; font-size: 0.9rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 1.5rem; 
            border: 1px solid #eee;
            transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.4s, background-color 0.5s;
        }
        .btn-back:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 8px 20px rgba(128, 0, 0, 0.15); 
            background-color: #fffafb; 
            color: var(--primary-maroon); 
            border-color: var(--primary-maroon);
        }
        .btn-back .arrow-icon { display: inline-block; transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .btn-back:hover .arrow-icon { transform: translateX(-3px); }

        /* ================= REPORT GRID & CARD ================= */
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .report-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-top: 5px solid var(--accent-gold);
            display: flex; flex-direction: column;
            
            /* Base Transitions */
            transform: translateY(0);
            transition: 
                transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                background-color 0.5s ease;
        }

        /* 3. Shadow Morph & Lift + 4. Breathing Tint */
        .report-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(128, 0, 0, 0.1), 0 4px 8px rgba(0,0,0,0.05);
            background-color: #fffafb; /* Subtle red tint */
        }

        /* Card Header Section */
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        /* 2. Icon Micro-Motion */
        .pub-icon {
            width: 45px; height: 45px; background: #fff8d6; 
            border-radius: 10px; color: #b4860b;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .report-card:hover .pub-icon {
            transform: translateY(-3px) rotate(5deg);
        }

        /* 1. Staggered Animation: Title */
        .pub-title {
            font-size: 1.15rem; font-weight: 700; color: var(--primary-maroon);
            margin: 0; flex-grow: 1; margin-left: 15px;
            transition: transform 0.3s ease;
        }
        .report-card:hover .pub-title {
            transform: translateX(4px);
            transition-delay: 0.05s;
        }

        /* 1. Staggered Animation: Count Badge */
        .book-count {
            font-size: 0.8rem; font-weight: 700;
            background: var(--primary-maroon); color: white;
            padding: 4px 10px; border-radius: 20px;
            transition: transform 0.3s ease;
        }
        .report-card:hover .book-count {
            transform: scale(1.05);
            transition-delay: 0.1s;
        }

        /* 1. Staggered Animation: List */
        .book-list {
            list-style: none; margin: 0; padding: 0;
            transition: transform 0.3s ease;
        }
        .report-card:hover .book-list {
            transform: translateX(2px);
            transition-delay: 0.15s;
        }

        .book-item {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            font-size: 0.9rem;
            color: var(--text-muted);
            display: flex; justify-content: space-between;
        }
        .book-item:last-child { border-bottom: none; }
        .book-title { font-weight: 600; color: var(--text-main); display: block; }
        .book-meta { font-size: 0.8rem; color: #888; }
        .no-books { color: #999; font-style: italic; text-align: center; margin-top: 10px; }

        @media print {
            header, .btn-back { display: none; }
            .report-grid { display: block; }
            .report-card { break-inside: avoid; border: 1px solid #ddd; margin-bottom: 20px; box-shadow: none; }
        }
    </style>
</head>
<body>

<header>
    <h1>Publisher Reports</h1>
    <p>Comprehensive Book Inventory Summary</p>
</header>

<div class="container">
    
    <a href="index.php" class="btn-back">
        <span class="arrow-icon">&larr;</span> &nbsp; Back to Dashboard
    </a>

    <div class="report-grid">
        <?php
        // 1. Get List of All Publishers
        $pubSql = "SELECT pub_id, pub_name FROM publishers ORDER BY pub_name";
        $publishers = $conn->query($pubSql);

        if ($publishers) {
            while($pub = $publishers->fetch_assoc()) {
                $pid = $pub['pub_id'];
                
                // 2. Count Books
                $countSql = "SELECT COUNT(*) as total FROM titles WHERE pub_id = '$pid'";
                $countRes = $conn->query($countSql);
                $count = $countRes->fetch_assoc()['total'];

                // 3. Get Books
                $bookSql = "SELECT title, type, price FROM titles WHERE pub_id = '$pid'";
                $books = $conn->query($bookSql);
                
                echo '<div class="report-card">';
                
                // Header with Icon and Badge
                echo '  <div class="card-header">
                            <div style="display:flex; align-items:center;">
                                <div class="pub-icon">🏢</div>
                                <h2 class="pub-title">' . htmlspecialchars($pub['pub_name']) . '</h2>
                            </div>
                            <span class="book-count">' . $count . ' Books</span>
                        </div>';
                
                // Book List
                echo '  <ul class="book-list">';
                
                if ($books->num_rows > 0) {
                    while($b = $books->fetch_assoc()) {
                        $price = isset($b['price']) ? number_format($b['price'], 2) : '0.00';
                        echo '<li class="book-item">
                                <div>
                                    <span class="book-title">' . htmlspecialchars($b['title']) . '</span>
                                    <span class="book-meta">' . ucfirst($b['type']) . '</span>
                                </div>
                                <div style="font-weight:600; color:var(--primary-maroon);">
                                    $' . $price . '
                                </div>
                              </li>';
                    }
                } else {
                    echo '<li class="no-books">No titles in inventory.</li>';
                }
                
                echo '  </ul>';
                echo '</div>'; // End Card
            }
        } else {
            echo "<p>No publishers found.</p>";
        }
        ?>
    </div>

</div>

</body>
</html>