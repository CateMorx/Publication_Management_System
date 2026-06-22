<?php 
include 'db_connect.php'; 

// --- LOGIC: HANDLE FORM SUBMISSIONS ---
// (Logic remains exactly the same)

if (isset($_POST['add_publisher'])) {
    $pid    = $_POST['pub_id_input'];
    $pname  = $_POST['pub_name'];
    $pcity  = $_POST['city'];
    $pstate = $_POST['state'];
    $pcountry = $_POST['country'];
    
    $stmt = $conn->prepare("INSERT INTO publishers (pub_id, pub_name, city, state, country) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $pid, $pname, $pcity, $pstate, $pcountry);
    
    try {
        if ($stmt->execute()) {
            echo "<script>alert('Publisher Added Successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: Duplicate ID or Invalid Data');</script>";
    }
    $stmt->close();
}

if (isset($_POST['add_title'])) {
    $tid    = $_POST['title_id'];
    $ttitle = $_POST['title_name'];
    $ttype  = $_POST['type'];
    $tprice = $_POST['price'];
    $tnotes = $_POST['notes'];     
    $tdate  = $_POST['pubdate'];   
    $tpubid = $_POST['current_pub_id']; 
    $tauid  = $_POST['au_id'];     
    
    $conn->begin_transaction();

    try {
        $stmt1 = $conn->prepare("INSERT INTO titles (title_id, title, type, price, notes, pubdate, pub_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssdsss", $tid, $ttitle, $ttype, $tprice, $tnotes, $tdate, $tpubid);
        $stmt1->execute();
        
        $stmt2 = $conn->prepare("INSERT INTO titleauthor (au_id, title_id, au_ord, royaltyper) VALUES (?, ?, 1, 50)");
        $stmt2->bind_param("ss", $tauid, $tid);
        $stmt2->execute();
        
        $conn->commit();
        echo "<script>alert('Title Added and Linked to Author Successfully!');</script>";

    } catch (Exception $e) {
        $conn->rollback(); 
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Publishers & Titles</title>
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
            overflow: hidden; /* Keeps shimmer inside */
        }

        /* Gold Shimmer Element */
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
            z-index: 0;
        }

        /* Ensure Text Stays Above Shimmer */
        header h1, header p { position: relative; z-index: 1; }
        header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.2rem; }
        header p { opacity: 0.9; font-size: 0.95rem; }

        @keyframes shine {
            0% { left: -100%; opacity: 0; }
            20% { opacity: 1; }
            50% { left: 200%; opacity: 0; }
            100% { left: 200%; opacity: 0; }
        }

        /* Main Layout */
        .container {
            max-width: 1200px; 
            margin: 2rem auto 4rem;
            padding: 0 1.5rem;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr; 
            gap: 2rem;
            align-items: start;
        }

        /* Cards */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-top: 5px solid var(--primary-maroon);
        }

        h2 { font-size: 1.1rem; font-weight: 700; color: var(--primary-maroon); margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        h3 { font-size: 1.1rem; color: var(--text-main); margin-bottom: 1rem; }
        h4 { font-size: 1rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 1rem; color: var(--dark-maroon); }

        /* Forms */
        label { font-weight: 600; display: block; margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted); }
        input, select, textarea { 
            width: 100%; padding: 10px; margin-top: 6px; 
            border: 1px solid #ddd; border-radius: 8px; 
            font-family: inherit; font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary-maroon); }

        /* ================= ANIMATED BUTTONS ================= */
        /* Applies to "Back" button and Form Submit buttons */
        .btn-back, button {
            transition: 
                transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                background-color 0.5s ease;
            position: relative;
            overflow: hidden; /* For tint effect */
        }

        /* 3. Shadow Morph & 2px Lift */
        .btn-back:hover, button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(128, 0, 0, 0.25);
        }

        /* 4. Background Tint Breathing (Simulated via overlay for gradient buttons) */
        button:hover {
            filter: brightness(1.1); /* Subtle brightness boost for gradient buttons */
        }
        .btn-back:hover {
            background-color: #fffafb; /* Subtle Red Tint for white button */
            border-color: var(--primary-maroon);
            color: var(--primary-maroon);
        }

        /* 2. Icon Micro-Motion (Arrow rotation) */
        .btn-back .arrow-icon {
            display: inline-block;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .btn-back:hover .arrow-icon {
            transform: translateX(-3px); /* Move left slightly */
        }

        /* Back Button Specifics */
        .btn-back {
            display: inline-flex;
            align-items: center;
            background: var(--white);
            color: var(--text-main);
            padding: 10px 20px;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-bottom: 1rem; 
            border: 1px solid #eee;
        }

        /* Submit Button Specifics */
        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
        }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: #f9f9f9; color: var(--primary-maroon); font-weight: 600; }
        .empty-row { text-align: center; color: var(--text-muted); padding: 20px; font-style: italic; }

        /* Helpers */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .span-2 { grid-column: span 2; }

        /* Responsive */
        @media (max-width: 900px) {
            .grid-layout { grid-template-columns: 1fr; }
            .mobile-hide { display: none; }
        }
    </style>
</head>
<body>

<header>
    <h1>Publishers & Titles</h1>
    <p>Inventory Management Module</p>
</header>

<div class="container">
    <div class="grid-layout">
        
        <div class="left-column">
            
            <a href="index.php" class="btn-back">
                <span class="arrow-icon">&larr;</span> &nbsp; Back to Dashboard
            </a>

            <div class="card" style="border-top-color: var(--accent-gold);">
                <h2>1. Create New Publisher</h2>
                <form method="POST">
                    <div class="grid-2">
                        <div>
                            <label>Publisher ID</label>
                            <input type="text" name="pub_id_input" required maxlength="4" placeholder="e.g. P999">
                        </div>
                        <div>
                            <label>Company Name</label>
                            <input type="text" name="pub_name" required placeholder="Name">
                        </div>
                    </div>

                    <label>City</label>
                    <input type="text" name="city" placeholder="City">

                    <div class="grid-2">
                        <div>
                            <label>State</label>
                            <input type="text" name="state" maxlength="5" placeholder="NY">
                        </div>
                        <div>
                            <label>Country</label>
                            <input type="text" name="country" placeholder="USA">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_publisher">Save Publisher</button>
                </form>
            </div>
        </div>

        <div class="right-column">
            
            <div class="btn-back mobile-hide" style="visibility: hidden; pointer-events: none; border-color: transparent;">
                &larr; Back to Dashboard
            </div>

            <div class="card" style="border-top-color: var(--accent-gold);">
                <h2>2. Manage Inventory</h2>
                
                <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                    <label style="margin-top:0;">Select Publisher to Edit:</label>
                    <div style="display:flex; gap:10px;">
                        <select name="pub_id" onchange="this.form.submit()" style="margin-top:0;">
                            <option value="">-- Choose a Publisher --</option>
                            <?php
                            $result = $conn->query("SELECT pub_id, pub_name FROM publishers");
                            if($result) {
                                while($row = $result->fetch_assoc()) {
                                    $selected = (isset($_POST['pub_id']) && $_POST['pub_id'] == $row['pub_id']) ? 'selected' : '';
                                    echo "<option value='{$row['pub_id']}' $selected>{$row['pub_name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php if(isset($_POST['current_pub_id']) && !isset($_POST['pub_id'])) {
                        echo "<input type='hidden' name='pub_id' value='" . $_POST['current_pub_id'] . "'>";
                    } ?>
                </form>

                <?php 
                $pid = "";
                if(isset($_POST['pub_id'])) { $pid = $_POST['pub_id']; } 
                elseif(isset($_POST['current_pub_id'])) { $pid = $_POST['current_pub_id']; }

                if($pid != ""): 
                    $stmt = $conn->prepare("SELECT pub_name FROM publishers WHERE pub_id = ?");
                    $stmt->bind_param("s", $pid);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $pName = $res->fetch_assoc()['pub_name'];
                    $stmt->close();
                ?>
                    <div style="margin-top: 1.5rem;">
                        <h3>Current Titles: <span style="color:var(--primary-maroon)"><?php echo $pName; ?></span></h3>

                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT title, type, price, pubdate FROM titles WHERE pub_id = '$pid'";
                                $titles = $conn->query($sql);
                                
                                if ($titles->num_rows > 0) {
                                    while($t = $titles->fetch_assoc()) {
                                        $dateDisplay = $t['pubdate'] ? date('M d', strtotime($t['pubdate'])) : '-';
                                        echo "<tr>
                                                <td>{$t['title']}</td>
                                                <td>{$t['type']}</td>
                                                <td>\${$t['price']}</td>
                                                <td>{$dateDisplay}</td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='empty-row'>No titles yet.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <div style="margin-top: 2rem; border-top: 2px dashed #ddd; padding-top: 1rem;">
                            <h4>Add New Title to Inventory</h4>
                            <form method="POST">
                                <input type="hidden" name="current_pub_id" value="<?php echo $pid; ?>">
                                
                                <div class="grid-2">
                                    <div>
                                        <label>Title ID</label>
                                        <input type="text" name="title_id" required maxlength="6" placeholder="T999">
                                    </div>
                                    <div>
                                        <label>Book Name</label>
                                        <input type="text" name="title_name" required placeholder="Title">
                                    </div>
                                </div>
                                
                                <div class="span-2">
                                    <label>Primary Author</label>
                                    <select name="au_id" required>
                                        <option value="">-- Select Author --</option>
                                        <?php
                                        $authors = $conn->query("SELECT au_id, au_fname, au_lname FROM authors");
                                        while($a = $authors->fetch_assoc()){
                                            echo "<option value='{$a['au_id']}'>{$a['au_fname']} {$a['au_lname']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="grid-2">
                                    <div>
                                        <label>Type</label>
                                        <input type="text" name="type" placeholder="e.g. business">
                                    </div>
                                    <div>
                                        <label>Price</label>
                                        <input type="number" step="0.01" name="price" placeholder="0.00">
                                    </div>
                                </div>
                                
                                <label>Publication Date</label>
                                <input type="date" name="pubdate" value="<?php echo date('Y-m-d'); ?>">

                                <label>Notes</label>
                                <textarea name="notes" rows="2" placeholder="Description..."></textarea>
                                
                                <button type="submit" name="add_title">Add Title</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #999;">
                        <p>Select a publisher above to view and add titles.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>