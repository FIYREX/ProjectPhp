<?php
$page_title = 'Make Order';
include('./includes/header.html');
?>

<html>
<head>
<title>Order Management</title>
</head>
<body>
<h1>Order Management</h1>
<form action="Order.php" method="post">


    <?php
    require_once('mysqli.php');
    global $dbc;

    session_start();
    if (isset($_SESSION['agent_id'])) {
        $agentId = $_SESSION['agent_id'];
    } else {
        // Redirect the user to the login page or handle authentication logic
        header('Location: login.php');
        exit();
    }
    ?>


<p>Customer Name: <input type="text" name="customerName" size="20" maxlength="40" value="<?php if (isset($_POST['customerName'])) echo $_POST['customerName']; ?>"></p>
<p>Customer.Address: <textarea name="customerAddress" rows="5" cols="50"><?php if (isset($_POST['customerAddress'])) echo $_POST['customerAddress']; ?></textarea></p>
<p>Customer Phone Number: <input type="text" name="customerPhone" size='10' maxlength='15' value="<?php if (isset($_POST['customerPhone'])) echo $_POST['customerPhone']; ?>"></p>
<p>Order Date: <input type="date" name="date" size="4" maxlength="4" value="<?php if (isset($_POST['date'])) echo $_POST['date']; ?>"/></p>

<p>Product:
<select name="productID">
    <?php
    $productQuery = "SELECT product_id, product_name FROM products";
    $productResult = $dbc->query($productQuery);

    while ($productRow = $productResult->fetch_assoc()) {
        $productId = $productRow['product_id'];
        $productName = $productRow['product_name'];
        echo "<option value='$productId'>$productName</option>";
    }
    ?>
</select>
</p>

<p>Product Quantity: <input type="text" name="productQuantity" size="4" maxlength="4" value="<?php if (isset($_POST['productQuantity'])) echo $_POST['productQuantity']; ?>"/></p>
<p><input type="submit" name="submit" value="Submit"/></p>
<input type="hidden" name="submitted" value="TRUE" />

</form>
</body>
</html>

<?php
if (isset($_POST['submitted'])) {
    // Include the database connection file
    require_once('mysqli.php');
    global $dbc;

    // Validate the input
    $errors = array();
   

    // Check if the customer name is empty
    if (empty($_POST['customerName'])) {
        $errors[] = "You forgot to enter your name.";
    }

    // Check if the customer address is empty
    if (empty($_POST['customerAddress'])) {
        $errors[] = "You forgot to enter your address.";
    }

    // Check if the customer phone number is empty
    if (empty($_POST['customerPhone'])) {
        $errors[] = "You forgot to enter your phone number.";
    } else {
        // Check if the customer phone number is a number
        if (!is_numeric($_POST['customerPhone'])) {
            $errors[] = "The phone number must be a number.";
        }
    }

    if (empty($_POST['date'])) {
        $errors[] = "You forgot to enter the date.";
    }

    // Check if the product ID is empty
    if (empty($_POST['productID'])) {
        $errors[] = "You forgot to select the product.";
    } 
    

    // Check if the product quantity is empty
if (empty($_POST['productQuantity'])) {
    $errors[] = "You forgot to enter the product quantity.";
} else {
    // Check if the product quantity is a number
    if (!is_numeric($_POST['productQuantity'])) {
        $errors[] = "The product quantity must be a number.";
    } else {
        // Check if the product quantity is within the limit
        $productQuantity = intval($_POST['productQuantity']);
        if ($productQuantity < 1 || $productQuantity > 10) {
            $errors[] = "The product quantity must be between 1 and 10.";
        }
    }
    }

    // If there are any errors, display them to the user
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<p class="error">' . $error . '</p>';
        }
    } else {
        // Check if the product is in stock
        $productQuery = "SELECT * FROM products WHERE product_id = '$productId'";
        $productResult = $dbc->query($productQuery);
        $product = $productResult->fetch_assoc();

        // If the product is in stock, create the order
        if ($product['quantity'] >= $_POST['productQuantity']) {
            // Insert the order into the database
            $customerName = $_POST['customerName'];
            $customerAddress = $_POST['customerAddress'];
            $customerPhone = $_POST['customerPhone'];
            $orderDate = $_POST['date'];
            $productQuantity = $_POST['productQuantity'];

            $sql = "INSERT INTO orders (agent_id, customer_name, customer_address, customer_phone, order_date, product_id, order_quantity, status) VALUES ('$agentId', '$customerName', '$customerAddress', '$customerPhone', '$orderDate', '$productId', '$productQuantity', 'pending')";
            $stmt = $dbc->prepare($sql);
            $stmt->execute();

            // Update the product quantity
            $newQuantity = $product['quantity'] - $productQuantity;
            $updateQuery = "UPDATE products SET quantity = '$newQuantity' WHERE product_id = '$productId'";
            $dbc->query($updateQuery);

            // Redirect the user to the order list page
            echo 'Waiting for supplier approval';
        } else {
            echo '<h1>Error!</h1>
            <p class="error">The product is out of stock. We apologize for any inconvenience.</p>';
        }
    }

    mysqli_close($dbc); // Close the database connection.
}

include('./includes/footer.html'); // Include the HTML footer.
?>
