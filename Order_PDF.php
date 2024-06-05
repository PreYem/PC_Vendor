<?php
require_once ('F_PDF/fpdf.php');

include_once ('DB.php');


session_start();

function formatNumber($number)
{
  return number_format($number, 0, '', ' ');
}


if (!isset($_SESSION['User_ID'])) {
  header('Location: ./');
}

if (isset($_GET['id'])) {
  $Order_ID = $_GET['id'];
  $Current_User = $_SESSION['User_ID'];

  $Q_User_FullName = "SELECT User_ID ,User_FirstName, User_LastName, User_Role FROM Users WHERE User_ID = $Current_User";
  $pdoQ_User_FullName = $connexion->prepare($Q_User_FullName);
  $pdoQ_User_FullName->execute();
  $User = $pdoQ_User_FullName->fetch(PDO::FETCH_ASSOC);
  $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];


  if ($Current_User != $User['User_ID'] && $User['User_Role'] === 'Client') {
    header('Location: ./');
    exit();
  }






  $pdf = new FPDF('P', 'mm', 'A4');
  $pdf->AddPage();

  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(0, 10, 'Order : ' . $Order_ID, 0, 1, 'C');



  $logoX = $pdf->getPageWidth() - 100; // Adjust the value according to the width of your logo
  $logoY = 5; // Adjust the value according to the desired distance from the top

  // Add the logo image
  $pdf->Image('Logo.png', 10, 5, 10, 0, );




  $pdf->SetFont('Arial', '', 12);




  if ($User['User_Role'] === 'Client') {
    $bigQuery = "SELECT p.Product_ID, p.Product_Name, p.Product_Picture, o.Order_ID, o.Order_Date, o.Order_TotalAmount, o.Order_Status, 
    oi.OrderItem_Quantity, oi.OrderItem_UnitPrice, u.User_ID , u.User_FirstName , u.User_LastName
    FROM Orders o
    INNER JOIN OrderItems oi ON o.Order_ID = oi.Order_ID
    INNER JOIN Products p ON oi.Product_ID = p.Product_ID
    INNER JOIN Users u ON o.User_ID = u.User_ID
    WHERE o.User_ID = $Current_User AND o.Order_ID = $Order_ID";

  } else {
    $bigQuery = "SELECT p.Product_ID, p.Product_Name, p.Product_Picture, o.Order_ID, o.Order_Date, o.Order_TotalAmount, o.Order_Status, 
    oi.OrderItem_Quantity, oi.OrderItem_UnitPrice, u.User_ID, u.User_FirstName , u.User_LastName
    FROM Orders o
    INNER JOIN OrderItems oi ON o.Order_ID = oi.Order_ID
    INNER JOIN Products p ON oi.Product_ID = p.Product_ID
    INNER JOIN Users u ON o.User_ID = u.User_ID
    WHERE  o.Order_ID = $Order_ID";
  }




  $pdobigQuery = $connexion->prepare($bigQuery);
  $pdobigQuery->execute();
  $Orders = $pdobigQuery->fetchAll(PDO::FETCH_ASSOC);


  



  // Group the Orders by Order_ID
  $orders = [];
  foreach ($Orders as $Order) {
    $orders[$Order['Order_ID']]['Order_Date'] = $Order['Order_Date'];
    $orders[$Order['Order_ID']]['Order_TotalAmount'] = $Order['Order_TotalAmount'];
    $orders[$Order['Order_ID']]['Order_Status'] = $Order['Order_Status'];
    $orders[$Order['Order_ID']]['User_ID'] = $Order['User_ID'];
    $orders[$Order['Order_ID']]['Products'][] = [
      'Product_ID' => $Order['Product_ID'],
      'Product_Name' => $Order['Product_Name'],
      'Product_Picture' => $Order['Product_Picture'],
      'OrderItem_Quantity' => $Order['OrderItem_Quantity'],
      'OrderItem_UnitPrice' => $Order['OrderItem_UnitPrice']
    ];


  }

  $User_FullName = $Order['User_FirstName'] . ' ' . $Order['User_LastName'];
  $pdf->Cell(0, 10, 'Client : ' . $User_FullName, 0, 1, 'C');
  $pdf->Cell(0, 10, 'Status : ' . $Order['Order_Status'], 0, 1, 'C');

} else {
  header('Location: ./');
  exit();
}





ob_start();


if ($pdobigQuery->rowCount() > 0) {

  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(10, 8, 'ID', 1, 0, 'C');
  $pdf->Cell(100, 8, 'Name', 1, 0, 'C');
  $pdf->Cell(10, 8, 'Q.', 1, 0, 'C');
  $pdf->Cell(28, 8, 'Unit Price (Dhs)', 1, 0, 'C');
  $pdf->Cell(20, 8, 'Total (Dhs)', 1, 1, 'C');


  $pdf->SetFont('Arial', '', 12);


  foreach ($orders as $order) {
    $total = 0;
    foreach ($order['Products'] as $product) {

      $productName = substr($product['Product_Name'], 0, 40) . '...'; // Truncate to 20 characters

      $imagePath = 'Product/' . $product['Product_Picture'];




      $pdf->Cell(10, 8, $product['Product_ID'], 1, 0, 'L');
      $pdf->Cell(100, 8, $productName, 1, 0);
      $pdf->Cell(10, 8, $product['OrderItem_Quantity'], 1, 0, 'L');
      $pdf->Cell(28, 8, formatNumber($product['OrderItem_UnitPrice']), 1, 0, 'L');
      $pdf->Cell(20, 8, formatNumber($product['OrderItem_Quantity'] * $product['OrderItem_UnitPrice']), 1, 1, 'L');
      $pdf->Ln(5);


      $total += $product['OrderItem_Quantity'] * $product['OrderItem_UnitPrice'];

    }

  }
  ;


  $pdf->SetY(5); // Move to 15 units from the bottom of the page
  $pdf->Cell(0, 10, 'Total Price: ' . formatNumber($total) . ' Dhs', 0, 1, 'R');
  ob_end_clean();
  $pdf->Output('filename.pdf', 'I');
} else {
  $pdf->Cell(0, 10, 'No products found in the database.', 0, 1);
}

