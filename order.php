<!-- taking the customer ID input -->
<div class="container">
  <?php
  function createSQLConnection()
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mybasket";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } else {
      return $conn;
    }
  }
  // initializing the session variable at the start of each page
  session_start();

  // ask for customer ID from the user
  if (!isset($_GET['custid']) && !isset($_GET['pid'])) {
    include 'input_customer.php';

    $conn = createSQLConnection();

    // generating the orderID
    $sql = "SELECT MAX(ORDERID) AS max_order_id FROM orders";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxOrderID = $row['max_order_id'];
    $orderID = $maxOrderID + 1;


    // storing in session global varibale in order to be accessible site-wide.
    $_SESSION['orderid'] = $orderID;

    // TODO: debugging
    echo "\nset order id as " . $_SESSION['orderid'];


  }
  ?>
</div>

<!-- taking input for product if custmoer is valid -->
<div class='container'>
  <?php

  // after customerID is provided, ask other details
  if (isset($_GET['custid'])) {
    $check = $_GET['custid'];
    $_SESSION['custid'] = $check;
    $flag = 0;

    //TODO: debugging 
    echo "\nset cust id as " . $_SESSION['custid'];

    $conn = createSQLConnection();
    $sql = "SELECT CUSTID FROM customer";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      // output data of each row
      while ($row = $result->fetch_assoc()) {
        if ($check == $row['CUSTID']) {
          include 'orderform.php';
          $flag = 1;
        }
      }

      // generating the sub-orderID each time this form is called.
      $sql = 'SELECT MAX(SUB_ORDID) AS max_sub_ord_id FROM orders WHERE orderid=' . $_SESSION['orderid'];
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $maxSubOrdId = $row['max_sub_ord_id'];
      $_SESSION['sub_ordid'] = $maxSubOrdId + 1;

      // TODO: debugging
      echo "\nset sub order id as " . $_SESSION['sub_ordid'];

    } else {
      echo "0 results";
    }
    if ($flag == 0) {
      echo "<h4> No Customer Found</h4>";
    }

    $conn->close();
  }

  // place the order using the pid
  if (isset($_GET['pid'])) {


    $conn = createSQLConnection();
    $pid = $_GET['pid'];
    $qty = $_GET['qty'];
    $total = 0;
    $flag = 0;
    $sql = "SELECT PID,PPRICE from product where PID=" . $pid . ";";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      // output data of each row
      $row = $result->fetch_assoc();
      $price = $row["PPRICE"];
      $total += $price * $qty;
      $flag = 1;


      $orderID = $_SESSION['orderid'];
      $custid = $_SESSION['custid'];
      $sub_ordid = $_SESSION['sub_ordid'];


      // if ($_SESSION["orderid"] != $conn->query("SELECT MAX(ORDERID) AS max_order_id FROM orders")->fetch_assoc()["max_order_id"])
      $sql = "INSERT INTO orders (ORDERID,CUSTID,PID,QUANTITY,PRICE, SUB_ORDID) VALUES($orderID,$custid,$pid,$qty,$total, $sub_ordid);";
      $result = $conn->query($sql);
      echo mysqli_error($conn);
      echo "<h4>ORDER PLACED SUCCESSFULLY! <a href='index.php?clicked=4&custid=" . $custid . "'>Place Another Order</a> </h4> ";
    } else {
      echo "Product Not Found!";
    }
    if ($flag == 1) {
      echo "<h4> Total Cost:" . $total . " </h4><br />";

    }
  }
  ?>
</div>

<!-- for showing the receipt -->
<!-- calling a function on the click of a button -->
<div class='container'>
  <form method='post' action=''>
    <input type='submit' class='button' name="show_receipt" value='Show Receipt' />
  </form>


  <?php
  // TODO: recept logic
  // calling a function on the click of a button
  if (array_key_exists('show_receipt', $_POST))
    show_reciept();
  function show_reciept()
  {
    $conn = createSQLConnection();
    $sql = "SELECT CUSTID, ORDERID,SUB_ORDID,QUANTITY, PRICE FROM orders where ORDERID= " . $_SESSION['orderid'] . ";";
    $result = $conn->query($sql);

    $sql2 = "SELECT SUM(PRICE) from orders where ORDERID={$_SESSION['orderid']};";

    if ($result->num_rows > 0) {
      echo "<table>";
      echo '<tr><th>Customer ID</th><th>Order ID</th><th>Sub-Order ID</th><th>Quantity</th><th>Price</th></tr>';

      while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $k => $v) {
          echo "<td>" . $v . "</td>";
        }
        echo "</tr>";
      }
      echo "</table>";
      echo "<h2>Total Amount: " . $conn->query($sql2)->fetch_assoc()['SUM(PRICE)'] . "</h2>";
    } else
      echo "<h4>No orders yet!</h4>";
  }
  ?>
</div>

<!-- for showing the products table -->
<div class='container-right'>

  <?php
  // : products table logic
  $conn = createSQLConnection();
  $sql = 'SELECT PID, PNAME, PPRICE FROM product;';
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Name</th><th>Price</th></tr>';

    // change array to assoc because array return id too hence twice columns
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>";
      foreach ($row as $v)
        echo "<td>" . $v . "</td> ";
      echo "</tr>";
    }
    echo '</table>';
  } else
    echo "<h4>Table is empty</h4>";

  ?>
  </table>
</div>