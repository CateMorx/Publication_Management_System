<?php 
include 'db_connect.php'; 

// --- 1. HANDLE FORM SUBMISSIONS ---

// A. Handle "Add Author" (Parent)
if (isset($_POST['add_author'])) {
    $aid    = $_POST['au_id_input'];
    $afname = $_POST['au_fname'];
    $alname = $_POST['au_lname'];
    $aphone = $_POST['phone'];
    $aaddr  = $_POST['address'];
    $acity  = $_POST['city'];
    $astate = $_POST['state'];
    $azip   = $_POST['zip'];
    $acontract = isset($_POST['contract']) ? 1 : 0; 

    $stmt = $conn->prepare("INSERT INTO authors (au_id, au_fname, au_lname, phone, address, city, state, zip, contract) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $aid, $afname, $alname, $aphone, $aaddr, $acity, $astate, $azip, $acontract);
    
    try {
        if ($stmt->execute()) {
            echo "<script>alert('Author Added Successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: Duplicate ID or Invalid Data');</script>";
    }
    $stmt->close();
}

// B. Handle "Add Title" (Child/Subform)
if (isset($_POST['add_title'])) {
    $tid    = $_POST['title_id'];
    $ttitle = $_POST['title_name'];
    $ttype  = $_POST['type'];
    $tprice = $_POST['price'];
    $tpub   = $_POST['pub_id']; 
    $tdate  = $_POST['pubdate']; 
    $tnotes = $_POST['notes'];   
    $tauid  = $_POST['current_au_id']; 
    
    $conn->begin_transaction();
    
    try {
        // 1. Insert into TITLES
        // FIXED: Removed the space in "sssdsss"
        $stmt1 = $conn->prepare("INSERT INTO titles (title_id, title, type, price, pub_id, pubdate, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssdsss", $tid, $ttitle, $ttype, $tprice, $tpub, $tdate, $tnotes);
        $stmt1->execute();
        
        // 2. Insert into TITLEAUTHOR (Links the new book to the selected author)
        $stmt2 = $conn->prepare("INSERT INTO titleauthor (au_id, title_id, au_ord, royaltyper) VALUES (?, ?, 1, 50)");
        $stmt2->bind_param("ss", $tauid, $tid);
        $stmt2->execute();
        
        $conn->commit();
        echo "<script>alert('Title Added and Linked to Author!');</script>";
        
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
    <title>Manage Authors & Titles</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- STYLES (Identical to Task 1 & 2 for consistency) --- */
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

        /* Header with Gold Shimmer */
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
            content: "";
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(250, 204, 21, 0.2) 50%, rgba(255, 255, 255, 0) 100%);
            transform: skewX(-25deg);
            animation: shine 6s infinite ease-in-out;
            pointer-events: none;
            z-index: 0;
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
        .container { max-width: 1200px; margin: 2rem auto 4rem; padding: 0 1.5rem; }
        .grid-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start; }

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

        /* Animations for Buttons */
        .btn-back, button {
            transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), background-color 0.5s ease;
            position: relative; overflow: hidden;
        }
        .btn-back:hover, button:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(128, 0, 0, 0.25); }
        button:hover { filter: brightness(1.1); }
        .btn-back:hover { background-color: #fffafb; border-color: var(--primary-maroon); color: var(--primary-maroon); }
        .btn-back .arrow-icon { display: inline-block; transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .btn-back:hover .arrow-icon { transform: translateX(-3px); }

        /* Buttons Specifics */
        .btn-back { display: inline-flex; align-items: center; background: var(--white); color: var(--text-main); padding: 10px 20px; border-radius: var(--radius); text-decoration: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 1rem; border: 1px solid #eee; }
        button { margin-top: 1.5rem; width: 100%; padding: 12px; background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon)); color: var(--white); border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background-color: #f9f9f9; color: var(--primary-maroon); font-weight: 600; }
        .empty-row { text-align: center; color: var(--text-muted); padding: 20px; font-style: italic; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .span-2 { grid-column: span 2; }

        @media (max-width: 900px) {
            .grid-layout { grid-template-columns: 1fr; }
            .mobile-hide { display: none; }
        }
    </style>
</head>
<body>

<header>
    <h1>Authors & Titles</h1>
    <p>Intellectual Property Module</p>
</header>

<div class="container">
    <div class="grid-layout">
        
        <div class="left-column">
            <a href="index.php" class="btn-back">
                <span class="arrow-icon">&larr;</span> &nbsp; Back to Dashboard
            </a>

            <div class="card" style="border-top-color: var(--accent-gold);">
                <h2>1. Create New Author</h2>
                <form method="POST">
                    <div class="grid-2">
                        <div>
                            <label>Author ID</label>
                            <input type="text" name="au_id_input" required maxlength="11" placeholder="Unique ID">
                        </div>
                        <div>
                            <label>First Name</label>
                            <input type="text" name="au_fname" required placeholder="John">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Last Name</label>
                            <input type="text" name="au_lname" required placeholder="Doe">
                        </div>
                        <div>
                            <label>Phone</label>
                            <input type="text" name="phone" placeholder="xxx-xxx-xxxx">
                        </div>
                    </div>

                    <label>Address</label>
                    <input type="text" name="address" placeholder="Street Address">

                    <div class="grid-2">
                        <div>
                            <label>City</label>
                            <input type="text" name="city" placeholder="City">
                        </div>
                        <div>
                            <label>State</label>
                            <input type="text" name="state" maxlength="2" placeholder="State">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Zip</label>
                            <input type="text" name="zip" maxlength="5" placeholder="Zip">
                        </div>
                        <div style="padding-top: 35px;">
                            <label style="display:inline; cursor:pointer;">
                                <input type="checkbox" name="contract" value="1" checked style="width:auto; margin-right:5px;"> Has Contract
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_author">Save Author</button>
                </form>
            </div>
        </div>

        <div class="right-column">
            
            <div class="btn-back mobile-hide" style="visibility: hidden; pointer-events: none; border-color: transparent;">
                &larr; Back to Dashboard
            </div>

            <div class="card" style="border-top-color: var(--accent-gold);">
                <h2>2. Manage Works</h2>
                
                <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                    <label style="margin-top:0;">Select Author to Edit:</label>
                    <div style="display:flex; gap:10px;">
                        <select name="au_id" onchange="this.form.submit()" style="margin-top:0;">
                            <option value="">-- Choose an Author --</option>
                            <?php
                            $result = $conn->query("SELECT au_id, au_fname, au_lname FROM authors");
                            while($row = $result->fetch_assoc()) {
                                $selected = "";
                                if(isset($_POST['au_id']) && $_POST['au_id'] == $row['au_id']) $selected = "selected";
                                elseif(isset($_POST['current_au_id']) && $_POST['current_au_id'] == $row['au_id']) $selected = "selected";
                                echo "<option value='{$row['au_id']}' $selected>{$row['au_fname']} {$row['au_lname']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>

                <?php 
                $aid = "";
                if(isset($_POST['au_id'])) $aid = $_POST['au_id'];
                elseif(isset($_POST['current_au_id'])) $aid = $_POST['current_au_id'];

                if($aid != ""): 
                    $stmt = $conn->prepare("SELECT au_fname, au_lname FROM authors WHERE au_id = ?");
                    $stmt->bind_param("s", $aid);
                    $stmt->execute();
                    $res = $stmt->get_result()->fetch_assoc();
                    $aName = $res['au_fname'] . " " . $res['au_lname'];
                ?>
                    <div style="margin-top: 1.5rem;">
                        <h3>Books by: <span style="color:var(--primary-maroon)"><?php echo $aName; ?></span></h3>

                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT t.title_id, t.title, t.type, t.price, t.pubdate 
                                        FROM titles t
                                        JOIN titleauthor ta ON t.title_id = ta.title_id
                                        WHERE ta.au_id = '$aid'";
                                $books = $conn->query($sql);
                                
                                if($books->num_rows > 0) {
                                    while($b = $books->fetch_assoc()) {
                                        $dateDisplay = $b['pubdate'] ? date('M d, Y', strtotime($b['pubdate'])) : '-';
                                        echo "<tr>
                                                <td>{$b['title_id']}</td>
                                                <td>{$b['title']}</td>
                                                <td>{$b['type']}</td>
                                                <td>\${$b['price']}</td>
                                                <td>{$dateDisplay}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='empty-row'>No books found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <div style="margin-top: 2rem; border-top: 2px dashed #ddd; padding-top: 1rem;">
                            <h4>Add New Title for this Author</h4>
                            
                            <form method="POST">
                                <input type="hidden" name="current_au_id" value="<?php echo $aid; ?>">
                                
                                <div class="grid-2">
                                    <div>
                                        <label>Title ID</label>
                                        <input type="text" name="title_id" required maxlength="6" placeholder="T555">
                                    </div>
                                    <div>
                                        <label>Title Name</label>
                                        <input type="text" name="title_name" required placeholder="Book Title">
                                    </div>
                                </div>

                                <div class="grid-2">
                                    <div>
                                        <label>Type</label>
                                        <input type="text" name="type" placeholder="e.g. psychology">
                                    </div>
                                    <div>
                                        <label>Price</label>
                                        <input type="number" step="0.01" name="price" placeholder="0.00">
                                    </div>
                                </div>

                                <label>Publication Date</label>
                                <input type="date" name="pubdate" value="<?php echo date('Y-m-d'); ?>">

                                <label>Publisher</label>
                                <select name="pub_id" required>
                                    <option value="">-- Select Publisher --</option>
                                    <?php
                                    $pubs = $conn->query("SELECT pub_id, pub_name FROM publishers");
                                    while($p = $pubs->fetch_assoc()){
                                        echo "<option value='{$p['pub_id']}'>{$p['pub_name']}</option>";
                                    }
                                    ?>
                                </select>

                                <label>Notes</label>
                                <textarea name="notes" rows="2" placeholder="Description..."></textarea>
                                
                                <button type="submit" name="add_title">Add Title & Link To Author</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #999;">
                        <p>Select an author above to view their works.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>