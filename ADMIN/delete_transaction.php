<?php
if (isset($_POST['delete'])) {
    // Get the transaction ID from the form
    $id = $_POST['id'];

    // Retrieve the transaction details
    $query = "SELECT * FROM transaction_history WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();

    if ($transaction) {
        // Insert the deleted transaction data into the deleted_transaction_history table
        $patient_name = $transaction['patient_name'];
        $contact_no = $transaction['contact_no'];
        $type_of_service = $transaction['type_of_service'];
        $date_of_service = $transaction['date_of_service'];
        $bill = $transaction['bill'];
        $change_amount = $transaction['change_amount'];
        $outstanding_balance = $transaction['outstanding_balance'];

        $insert_query = "INSERT INTO deleted_transaction_history (patient_name, contact_no, type_of_service, date_of_service, bill, change_amount, outstanding_balance) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $con->prepare($insert_query);
        $insert_stmt->bind_param("ssssddd", $patient_name, $contact_no, $type_of_service, $date_of_service, $bill, $change_amount, $outstanding_balance);

        // Execute the insert query
        if ($insert_stmt->execute()) {
            // Now delete the transaction from the transaction_history table
            $delete_query = "DELETE FROM transaction_history WHERE id = ?";
            $delete_stmt = $con->prepare($delete_query);
            $delete_stmt->bind_param("i", $id);

            if ($delete_stmt->execute()) {
                // Redirect to the same page after deleting
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                echo "Error deleting transaction: " . mysqli_error($con);
            }
        } else {
            echo "Error moving transaction to deleted transaction history: " . mysqli_error($con);
        }
        $insert_stmt->close();
    } else {
        echo "Transaction not found.";
    }
    $stmt->close();
}
?>