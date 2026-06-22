<?php 
include 'db_connect.php'; 

// --- HELPER FUNCTION: REAL-TIME "AGO" CALCULATOR ---
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'Just now';
}

// --- FETCH RECENT ACTIVITY ---
$sql = "
    (SELECT title AS item_name, created_at AS event_date, 'book' AS type 
     FROM titles)
    UNION
    (SELECT CONCAT(fname, ' ', lname) AS item_name, created_at AS event_date, 'employee' AS type 
     FROM employee)
    ORDER BY event_date DESC 
    LIMIT 5
";

$activities = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUBS Database Management System</title>
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

        /* ================= HEADER WITH SHIMMER ================= */
        header {
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            color: var(--white);
            padding: 3.2rem 1rem 3.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            position: relative;
            overflow: hidden; /* Keeps shimmer inside */
        }

        /* The Gold Shimmer Element */
        header::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(250, 204, 21, 0.2) 50%, /* Gold tint */
                rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-25deg);
            animation: shine 6s infinite ease-in-out;
            pointer-events: none;
            z-index: 0; /* Behind text */
        }

        /* Ensure Text Stays Above Shimmer */
        header h1, header p {
            position: relative;
            z-index: 1;
        }

        header h1 { font-size: 2.6rem; font-weight: 800; margin-bottom: 0.4rem; letter-spacing: -0.8px; }
        header p { font-size: 1.05rem; opacity: 0.85; font-weight: 400; }

        @keyframes shine {
            0% { left: -100%; opacity: 0; }
            20% { opacity: 1; }
            50% { left: 200%; opacity: 0; }
            100% { left: 200%; opacity: 0; }
        }

        /* ================= DASHBOARD ================= */
        .dashboard-wrapper { max-width: 1300px; margin: 3.5rem auto; padding: 0 2rem; display: grid; grid-template-columns: 1fr 320px; gap: 3rem; align-items: start; }
        .section-header { background: var(--white); padding: 1rem 1.6rem; border-radius: var(--radius) var(--radius) 0 0; border-left: 6px solid var(--primary-maroon); margin-bottom: 1.6rem; font-weight: 700; color: var(--primary-maroon); text-transform: uppercase; font-size: 0.85rem; box-shadow: 0 4px 14px rgba(0,0,0,0.05); }

        /* ================= CARD ANIMATIONS ================= */
        .nav-menu { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.6rem; }

        .menu-item {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2.3rem;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            min-height: 250px;
            
            /* 3. Shadow Morph & Base Transitions */
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            transform: translateY(0);
            transition: 
                transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                background-color 0.5s ease;
        }

        /* 4. Background Tint Breathing on Hover */
        .menu-item:hover {
            transform: translateY(-2px); /* 2px Lift */
            box-shadow: 0 16px 32px rgba(128, 0, 0, 0.12), 0 4px 8px rgba(0,0,0,0.05); /* Sharper shadow */
            background-color: #fffafb; /* Subtle Red/Maroon Tint (2-4%) */
        }

        /* 2. Icon Micro-Motion */
        .icon-container {
            width: 52px; height: 52px; background: #fff1f1; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.4rem; color: var(--primary-maroon);
            
            /* Smooth transition for motion */
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Rotate and Float Icon on Hover */
        .menu-item:hover .icon-container {
            transform: translateY(-4px) rotate(3deg);
        }

        .icon-container svg { width: 28px; height: 28px; stroke-width: 2; fill: none; stroke: currentColor; }

        /* 1. Staggered Content Reveal */
        /* We use a subtle text slide to visualize the stagger */
        .menu-item h2, .menu-item p {
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .menu-item h2 { font-size: 1.35rem; font-weight: 700; margin-bottom: 0.7rem; color: var(--primary-maroon); }
        .menu-item p { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 2.2rem; flex-grow: 1; }

        /* Delays apply when entering hover state */
        .menu-item:hover h2 {
            transform: translateX(3px);
            transition-delay: 0.05s; /* 50ms delay */
        }

        .menu-item:hover p {
            transform: translateX(3px);
            color: var(--text-main); /* Slightly darker text */
            transition-delay: 0.1s; /* 100ms delay */
        }

        .cta-label {
            font-size: 0.9rem; font-weight: 600; color: var(--white);
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            padding: 0.65rem 2.5rem; border-radius: 10px; width: fit-content;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .menu-item:hover .cta-label {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.22);
            transition-delay: 0.15s; /* Button moves last */
        }

        /* Activity Sidebar */
        .activity-sidebar { position: sticky; top: 2rem; }
        .activity-card { background: var(--white); border-radius: var(--radius); box-shadow: 0 6px 22px rgba(0,0,0,0.06); overflow: hidden; }
        .activity-item { padding: 1.3rem; display: flex; gap: 1rem; border-bottom: 1px solid #f1f1f1; }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 30px; height: 30px; border-radius: 50%; background: #fff1f1; color: var(--primary-maroon); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }
        .activity-text { font-size: 0.9rem; font-weight: 500; }
        .activity-time { display: block; font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; }
        
        footer { margin-top: 6rem; padding: 3rem; text-align: center; background: #eaeaea; color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid #ddd; }
        
        @media (max-width: 1100px) { .dashboard-wrapper { grid-template-columns: 1fr; max-width: 850px; } .activity-sidebar { position: static; } }
        @media (max-width: 650px) { .nav-menu { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <h1>PUBS Database Management System</h1>
    <p>Centralized Administration & Inventory Control Portal</p>
</header>

<div class="dashboard-wrapper">

    <main>
        <div class="section-header">System Modules</div>
        <nav class="nav-menu">
            <a href="task1.php" class="menu-item">
                <div class="icon-container"><svg viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg></div>
                <h2>Publishers & Titles</h2>
                <p>Manage book inventory and link titles to active publisher accounts.</p>
                <div class="cta-label">Manage</div>
            </a>
            <a href="task2.php" class="menu-item">
                <div class="icon-container"><svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                <h2>Publishers & Employees</h2>
                <p>Staff onboarding and role assignments across publisher branches.</p>
                <div class="cta-label">Manage</div>
            </a>
            <a href="task3.php" class="menu-item">
                <div class="icon-container"><svg viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></div>
                <h2>Authors & Titles</h2>
                <p>Intellectual property management and author-title associations.</p>
                <div class="cta-label">Manage</div>
            </a>
            <a href="task4.php" class="menu-item">
                <div class="icon-container"><svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></div>
                <h2>Publisher Reports</h2>
                <p>Data summaries and volume counts grouped by publisher.</p>
                <div class="cta-label">View</div>
            </a>
        </nav>
    </main>

    <aside class="activity-sidebar">
        <div class="section-header">Recent Activity</div>
        <div class="activity-card">
            <?php 
            if($activities && $activities->num_rows > 0) {
                while($act = $activities->fetch_assoc()) {
                    $icon = ($act['type'] == 'book') ? '📚' : '👤';
                    $msg = ($act['type'] == 'book') ? "New book added: <strong>" . htmlspecialchars($act['item_name']) . "</strong>" : "New employee hired: <strong>" . htmlspecialchars($act['item_name']) . "</strong>";
                    $timeDisplay = time_elapsed_string($act['event_date']);

                    echo '
                    <div class="activity-item">
                        <div class="activity-icon">' . $icon . '</div>
                        <div class="activity-text">
                            ' . $msg . '
                            <span class="activity-time">' . $timeDisplay . '</span>
                        </div>
                    </div>';
                }
            } else {
                echo '<div style="padding:20px; color:#666; text-align:center;">No recent activity found.</div>';
            }
            ?>
        </div>
    </aside>

</div>

<footer>
    &copy; 2025 PUBS Database Management System • Version 3.1.0
</footer>

</body>
</html>