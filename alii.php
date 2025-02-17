<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "ali";

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$subtotal = 0;
$total = 0;
$tax = 0;
$customer_name = "";
$items_ordered = [];

// Prices for items in INR
$prices = [
    "Latte" => 415.00, "Espresso" => 332.00, "Iced Latte" => 456.00, "Vale Coffee" => 290.00,
    "Cappuccino" => 498.00, "African Coffee" => 373.00, "American Coffee" => 249.00, "Iced Cappuccino" => 415.00,
    "African Iced Coffee" => 456.00, "American Iced Coffee" => 290.00, "School Cake" => 332.00, "Sunny AO Cake" => 539.00,
    "Jonathan YO Cake" => 581.00, "West African Cake" => 498.00, "Lagos Chocolate Cake" => 664.00, "Kilburn Chocolate Cake" => 623.00
];

// Background images for items
$images = [
    "Latte" => "n1.png", "Espresso" => "n2.png", "Iced Latte" => "n3.png", "Vale Coffee" => "n4.png",
    "Cappuccino" => "n5.png", "African Coffee" => "n6.png", "American Coffee" => "n7.png", "Iced Cappuccino" => "n8.png",
    "African Iced Coffee" => "n9.png", "American Iced Coffee" => "n10.png", "School Cake" => "n11.png", "Sunny AO Cake" => "n12.png",
    "Jonathan YO Cake" => "n13.png", "West African Cake" => "n14.png", "Lagos Chocolate Cake" => "n15.png", "Kilburn Chocolate Cake" => "n16.png"
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    // Get customer name
    $customer_name = !empty($_POST['customer_name']) ? trim(htmlspecialchars($_POST['customer_name'])) : 'Unknown Customer';

    // Reset items ordered
    $items_ordered = [];

    // Calculate subtotal and filter selected items
    foreach ($prices as $item => $price) {
        $input_name = strtolower(str_replace(' ', '_', $item));
        if (!empty($_POST[$input_name])) {
            $quantity = filter_input(INPUT_POST, $input_name, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

            if ($quantity !== false && $quantity > 0) {
                $items_ordered[$item] = $quantity;
                $subtotal += $price * $quantity;
            }
        }
    }

    // Calculate tax and total
    $tax = $subtotal * 0.10;
    $total = $subtotal + $tax;

    // Only store if items are selected
    if (!empty($items_ordered)) {
        $items_json = json_encode($items_ordered);

        $stmt = $conn->prepare("INSERT INTO bills (customer_name, items, subtotal, total) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("❌ SQL Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("ssdd", $customer_name, $items_json, $subtotal, $total);
        if (!$stmt->execute()) {
            die("❌ Execution Error: " . $stmt->error);
        } else {
            // ✅ Data inserted successfully, now store details in session
            $_SESSION['bill'] = [
                'customer_name' => $customer_name,
                'items_ordered' => $items_ordered,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ];
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAFE KATTA - Billing System</title>
    <style>
       /* Full-screen background */
       body {
            background: url('main.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .container {
            width: 95vw;
            height: 95vh;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            background:  url('main.png');
            display: flex;
            flex-direction: column;
            overflow: auto;
        }
        
        h1 {
    font-family: 'Lobster', cursive;
    font-size: 60px;
    color: #ffcc00;
    text-shadow: 0px 0px 10px #ff5733, 0px 0px 20px #ff8c00, 0px 0px 30px #ffcc00;
    animation: glow 1.5s infinite alternate, fadeIn 2s ease-in-out;
    text-align: center; /* Center text */
    width: 100%; /* Ensure full width */
}


        @keyframes glow {
            0% { text-shadow: 0px 0px 10px #ff5733, 0px 0px 20px #ff8c00, 0px 0px 30px #ffcc00; }
            100% { text-shadow: 0px 0px 20px #ff5733, 0px 0px 30px #ff8c00, 0px 0px 40px #ffcc00; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .menu-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
        }

        .menu-item:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 1);
        }

        .menu-item span {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .menu-item input {
            width: 50px;
            text-align: center;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .customer-input-container {
    position: relative;
    width: 90%;
    margin: 10px auto;
}

.customer-input-container input {
    width: 100%;
    padding: 15px 45px;
    font-size: 20px; /* Increase font size */
    font-family: 'Poppins', sans-serif; /* Change font */
    border: 2px solid #ffcc00;
    border-radius: 8px;
    outline: none;
    transition: 0.3s;
    background: rgba(10, 0, 0, 0.9);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.customer-input-container input {
    width: 100%;
    padding: 15px 45px;
    font-size: 20px; /* Increase font size */
    font-family: 'Poppins', sans-serif; /* Change font */
    border: 2px solid #ffcc00;
    border-radius: 8px;
    outline: none;
    transition: 0.3s;
    background: rgba(225, 41, 41, 0.9);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}


.customer-input-container .icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #ff8c00;
}



        input, button {
            padding: 12px;
            width: 90%;
            border-radius: 8px;
            font-size: 16px;
        }
        button[type="submit"] {
    background: #ffcc00;
    color: black;
    padding: 12px;
    width: 100%;
    font-size: 18px;
    border-radius: 8px;
    margin-top: 20px; /* Keeps spacing after input fields */
    cursor: pointer;
}

button[type="submit"]:hover {
    background: #ffdb4d;
}

.history-btn {
    background: #ff5722;
    color: white;
    border: none;
    padding: 12px;
    width: 100%;
    font-size: 18px;
    border-radius: 8px;
    margin-top: 30px; /* Increases spacing from "Generate Bill" */
    cursor: pointer;
}

.history-btn:hover {
    background: #ff7043;
}


        button {
            background: #ff5722;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        #receipt {
            max-width: 350px;
            margin: auto;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            display: none; /* Initially hidden */
        }
        .footer {
    margin-top: 20px;
    text-align: center;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 15px;
    border-radius: 12px;
    font-size: 14px;
    width: 100%;
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 10px;
}

.footer-item {
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease-in-out;
}

.footer-item:hover {
    transform: scale(1.05);
}

.footer-icon {
    font-size: 22px;
    background: rgba(255, 204, 0, 0.8);
    padding: 8px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: black;
}

.footer a {
    color: #ffcc00;
    text-decoration: none;
    font-weight: bold;
}

.footer a:hover {
    text-decoration: underline;
}

    </style>
    <script>
function printBill() {
    var billContent = document.getElementById("bill").innerHTML;
    if (!billContent.trim()) {
        alert("Bill is empty! Generate the bill first.");
        return;
    }
    var newWindow = window.open("", "_blank", "width=600,height=700");
    newWindow.document.write(`
        <html>
        <head>
            <title>Print Bill</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h2 { text-align: center; }
                ul { list-style: none; padding: 0; }
                p, li { font-size: 18px; }
            </style>
        </head>
        <body>
            <h2>🧾 Customer Bill</h2>
            ${billContent}
            <script>
                setTimeout(() => {
                    window.print();
                    window.close();
                }, 1000);
            <\/script>
        </body>
        </html>
    

    

</script>

</head>
<body>
    <div class="container">
        <h1>☕ CAFE KATTA ☕</h1>
        <form method="POST">
            <label><strong>Customer Name:</strong></label>
            <div class="customer-input-container">
                <span class="icon">👤</span>
            <input type="text" name="customer_name" placeholder="Enter customer's name" required><br>
            <h3>Select Items</h3>
            <div class="menu-container">
                <?php foreach ($prices as $item => $price): ?>
                    <div class="menu-item">
                        <span><?= $item ?><br>₹<?= number_format($price, 2) ?></span>
                        <input type="number" name="<?= strtolower(str_replace(' ', '_', $item)) ?>" min="0" placeholder="0">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="add">📝 Generate Bill</button>
        </form>
        <button class="history-btn" onclick="window.location.href='order_history.php'">📜 Order History</button>
        </div>
        <div class="footer">
    <div class="footer-item">
        <span class="footer-icon">📍</span>
        <p><strong>Address:</strong> Camp,Pune,India</p>
    </div>
    <div class="footer-item">
        <span class="footer-icon">📞</span>
        <p><strong>Mobile:</strong> <a href="tel:+917498111168">+917498111168</a></p>
    </div>
    <div class="footer-item">
        <span class="footer-icon">📧</span>
        <p><strong>Email:</strong> <a href="mailto:ali05@cafekatta.com">ali05@cafekatta.com</a></p>
    </div>
</div>


        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['bill'])): ?>
            <?php extract($_SESSION['bill']); ?>
            <script>document.addEventListener("DOMContentLoaded", function() { document.getElementById('receipt').style.display = 'block'; });</script>
            <div id="receipt">
                <h2>🧾 Invoice</h2>
                <p><strong>Customer:</strong> <?= htmlspecialchars($customer_name) ?></p>
                <table>
                    <thead>
                        <tr><th>Item</th><th>Qty</th><th>Price (₹)</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items_ordered as $item => $quantity): ?>
                            <tr><td><?= htmlspecialchars($item) ?></td><td><?= $quantity ?></td><td>₹<?= number_format($prices[$item] * $quantity, 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><strong>Subtotal:</strong> ₹<?= number_format($subtotal, 2) ?></p>
                <p><strong>Tax (10%):</strong> ₹<?= number_format($tax, 2) ?></p>
                <p class="total"><strong>Total:</strong> ₹<?= number_format($total, 2) ?></p>
                <button onclick="window.print();">🖨️ Print Bill</button>
            </div>
        <?php endif; ?>
    </div>
    <script>
    function printReceipt() {
        var receipt = document.getElementById("receipt").cloneNode(true);
        var printWindow = window.open('', '', 'width=400,height=600');
        printWindow.document.write(
            <html>
            <head>
                <title>Invoice</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                    .receipt-container {
                        max-width: 320px;
                        margin: auto;
                        padding: 15px;
                        border: 2px solid #333;
                        border-radius: 10px;
                        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
                        background: #fff;
                    }
                    h2 { margin-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border-bottom: 1px solid #ddd; padding: 8px; }
                    .total { font-weight: bold; font-size: 18px; margin-top: 10px; }
                    @media print {
                        body * { visibility: hidden; }
                        .receipt-container, .receipt-container * { visibility: visible; }
                        .receipt-container { position: absolute; left: 0; top: 0; width: 100%; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt-container">${receipt.innerHTML}</div>
                <script>window.onload = function() { window.print(); window.close(); }</script>
            </body>
            </html>
       
        
    
</script>


</body>
</html>
