<?php
session_start();
include 'db_connect.php';

// --- CONFIGURATION ---
$admin_password = ""; 

// --- 1. HANDLE LOGOUT ---
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: add_job.php");
    exit();
}

// --- 2. HANDLE LOGIN ---
if (isset($_POST['do_login'])) {
    $pass_input = $_POST['password'];
    if ($pass_input === $admin_password) {
        $_SESSION['is_admin'] = true;
    } else {
        $error = "Incorrect Password!";
    }
}

// --- 3. HANDLE ADD JOB (Only if Logged In) ---
$job_added = false; 

if (isset($_POST['add_job']) && isset($_SESSION['is_admin'])) {
    $jdesc = $_POST['job_desc'];
    $jmin  = $_POST['min_lvl'];
    $jmax  = $_POST['max_lvl'];

    $stmt = $conn->prepare("INSERT INTO jobs (job_desc, min_lvl, max_lvl) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $jdesc, $jmin, $jmax);

    if ($stmt->execute()) {
        $job_added = true; 
    } else {
        $error = "Database Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Add New Job</title>
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

        /* HEADER */
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
        .container { max-width: 500px; margin: 3rem auto 4rem; padding: 0 1.5rem; }

        /* Card Style */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-top: 5px solid var(--accent-gold);
        }

        h3 { font-size: 1.25rem; font-weight: 700; color: var(--primary-maroon); margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        p { color: var(--text-muted); margin-bottom: 1.5rem; }

        /* Forms */
        label { font-weight: 600; display: block; margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted); }
        input[type="text"], input[type="number"], input[type="password"] { 
            width: 100%; padding: 12px; margin-top: 6px; 
            border: 1px solid #ddd; border-radius: 8px; 
            font-family: inherit; font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary-maroon); }

        /* Standard Buttons */
        button {
            margin-top: 1.5rem; width: 100%; padding: 12px;
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            color: var(--white); border: none; border-radius: 8px;
            font-weight: 600; font-size: 1rem; cursor: pointer;
            transition: transform 0.4s, box-shadow 0.4s;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(128, 0, 0, 0.25); }

        /* Alerts */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }

        .logout-link { color: #dc3545; text-decoration: none; font-size: 0.9rem; font-weight: 600; }
        .logout-link:hover { text-decoration: underline; }
        .flex-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 1.5rem; }
        .flex-header h3 { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

        /* --- CUSTOM MODAL STYLES --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            display: none; /* Hidden by default */
            justify-content: center; align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        .modal-box {
            background: white; width: 90%; max-width: 400px;
            padding: 2rem; border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            text-align: center;
            border-top: 6px solid var(--accent-gold);
            animation: popIn 0.3s ease-out;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-box h2 { color: var(--primary-maroon); margin-bottom: 10px; font-size: 1.5rem; }
        .modal-box p { color: var(--text-muted); margin-bottom: 25px; }
        .modal-actions { display: grid; gap: 10px; }
        
        .btn-modal-primary {
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            color: white; width: 100%; padding: 12px; margin: 0;
        }
        .btn-modal-secondary {
            background: #f1f1f1; color: #555; width: 100%; padding: 12px; margin: 0;
        }
        .btn-modal-secondary:hover { background: #e0e0e0; color: #333; transform: translateY(-2px); }
    </style>
</head>
<body>

<header>
    <h1>Admin Access</h1>
    <p>Job Role Configuration</p>
</header>

<div class="container">

    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['is_admin'])): ?>
        <div class="card">
            <h3>🔒 Restricted Area</h3>
            <p>Please enter the administrator password to manage Job Roles.</p>
            <form method="POST">
                <label>Password:</label>
                <input type="password" name="password" placeholder="Enter Password">
                <button type="submit" name="do_login">Login</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="flex-header">
                <h3>Add New Job Role</h3>
                <a href="?action=logout" class="logout-link">Logout &rarr;</a>
            </div>
            
            <form method="POST">
                <label>Job Description:</label>
                <input type="text" name="job_desc" required placeholder="e.g. Senior Developer">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Min Level:</label>
                        <input type="number" name="min_lvl" id="minInput" required min="1" max="250" placeholder="1" oninput="adjustMaxLevel()">
                    </div>
                    <div>
                        <label>Max Level:</label>
                        <input type="number" name="max_lvl" id="maxInput" required min="1" max="250" placeholder="250">
                    </div>
                </div>
                
                <button type="submit" name="add_job">Add Job Role</button>
            </form>

            <script>
            function adjustMaxLevel() {
                var minInput = document.getElementById("minInput");
                var maxInput = document.getElementById("maxInput");
                var currentMin = parseInt(minInput.value);
                if (!isNaN(currentMin)) {
                    maxInput.min = currentMin;
                    if (parseInt(maxInput.value) < currentMin) {
                        maxInput.value = currentMin;
                    }
                }
            }
            </script>
        </div>
        <p style="margin-top:20px; text-align:center;">
            <small>Note: After adding a job, close this tab and <b>refresh the Manage Employees page</b>.</small>
        </p>
    <?php endif; ?>

</div>

<?php if($job_added): ?>
<div class="modal-overlay" style="display: flex;">
    <div class="modal-box">
        <h2>Success!</h2>
        <p>New Job Role has been added to the database.</p>
        <div class="modal-actions">
            <button class="btn-modal-primary" onclick="window.location.href='add_job.php'">
                Add Another Job
            </button>
            <button class="btn-modal-secondary" onclick="window.close()">
                Exit
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>