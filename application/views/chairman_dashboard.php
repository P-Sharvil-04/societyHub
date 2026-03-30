<!DOCTYPE html>
<html>

<head>
    <title>Chairman Dashboard</title>
</head>

<body>
    <h2>Current Balance:
        <?= ($balance !== null && $balance > 0) ? '₹' . number_format($balance, 2) : '₹0.00' ?>
    </h2>

    <?php if (!empty($balance_msg)): ?>
        <p style="color: #b00;"><?= $balance_msg ?></p>
    <?php endif; ?>

    <h3>Create Order (INR)</h3>
    <?php if ($this->session->flashdata('order_error')): ?>
        <div style="color:red;"><?= $this->session->flashdata('order_error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('Payment_controller/create_order') ?>">
        Amount (INR): <input type="text" name="amount" value="500" required>
        <button type="submit">Create Order</button>
    </form>

    <h3>Recent Orders</h3>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)) foreach ($orders as $o): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= $o['order_id'] ?></td>
                    <td>₹<?= number_format($o['amount'], 2) ?></td>
                    <td><?= $o['status'] ?></td>
                    <td><?= $o['payment_id'] ?: '-' ?></td>
                    <td>
                        <?php if ($o['status'] !== 'paid'): ?>
                            <form method="post" action="<?= base_url('Payment_controller/create_order') ?>">
                                <input type="hidden" name="amount" value="<?= $o['amount'] ?>">
                                <button type="submit">Pay Now</button>
                            </form>
                        <?php else: ?>
                            Paid
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
