<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Fetch orders in descending order (newest first)
$query = "SELECT * FROM bills ORDER BY id DESC"; 
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('main.png') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            font-size: 32px;
            color: #ff5733;
        }

        /* Fixed Back to Billing Button at Top Left */
        .back-btn {
            display: inline-block;
            padding: 10px 15px;
            font-size: 16px;
            text-decoration: none;
            background: #ff5733;
            color: white;
            border-radius: 5px;
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-btn:hover {
            background: #e04e26;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #ddd;
            text-align: center;
        }

        th, td {
            padding: 10px;
        }

        th {
            background: #ffcc00;
        }
    </style>
</head>
<body>

    <!-- Back to Billing Button -->
    <a href="/cafebill/alii.php" class="back-btn">⬅️ Back to Billing</a>


    <div class="container">
        <h1>📜 Order History</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Items Ordered</th>
                        <th>Subtotal (₹)</th>
                        <th>Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['items']) ?></td>
                            <td>₹<?= number_format($row['subtotal'], 2) ?></td>
                            <td>₹<?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php $conn->close(); ?>
