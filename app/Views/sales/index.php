<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-dark vh-100 p-3">
            <h4 class="text-white">PharmaTrack</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/products"><i class="fas fa-capsules"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/sales"><i class="fas fa-receipt"></i> Sales</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/reports"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 p-4">
            <h2>Sales Transactions</h2>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <?php if(empty($sales)): ?>
                        <div class="alert alert-info">No sales yet. Create your first sale from the POS (coming soon).</div>
                    <?php else: ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>ID</th><th>Invoice #</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($sales as $s): ?>
                                <tr>
                                    <td><?= $s['sale_id'] ?></td>
                                    <td><?= $s['invoice_number'] ?? 'N/A' ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($s['sale_date'])) ?></td>
                                    <td>₱<?= number_format($s['total_amount'], 2) ?></td>
                                    <td><?= ucfirst($s['payment_method'] ?? 'cash') ?></td>
                                    <td><span class="badge bg-success"><?= $s['status'] ?? 'completed' ?></span></td>
                                    <td>
                                        <a href="/sales/view/<?= $s['sale_id'] ?>" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>