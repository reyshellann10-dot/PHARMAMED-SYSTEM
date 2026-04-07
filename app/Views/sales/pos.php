<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<style>
    .product-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid #dee2e6;
        margin-bottom: 10px;
    }
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }
    .cart-item {
        border-bottom: 1px solid #dee2e6;
        padding: 10px 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .quantity-input {
        width: 60px;
        text-align: center;
    }
    .search-box {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        padding: 10px 0;
    }
    .products-list {
        max-height: 500px;
        overflow-y: auto;
    }
    .cart-summary {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        position: sticky;
        top: 20px;
    }
    .prescription-badge {
        font-size: 10px;
        padding: 2px 5px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Left Side - Products Selection -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-search"></i> Search Products
                </div>
                <div class="card-body">
                    <div class="search-box">
                        <div class="input-group mb-3">
                            <input type="text" id="searchProduct" class="form-control" 
                                   placeholder="Search by product name or code...">
                            <button class="btn btn-primary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="products-list" id="productsList">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>Start typing to search for products</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Shopping Cart -->
        <div class="col-md-5">
            <div class="cart-summary">
                <h5 class="mb-3">
                    <i class="fas fa-shopping-cart"></i> Current Sale
                </h5>
                
                <!-- Customer Selection -->
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <select id="customerId" class="form-select">
                        <option value="">Walk-in Customer</option>
                        <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['customer_id'] ?>">
                            <?= $customer['first_name'] . ' ' . $customer['last_name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Cart Items -->
                <div id="cartItems" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($cart)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                        <p>Cart is empty</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($cart as $item): ?>
                        <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <strong><?= $item['product_name'] ?></strong><br>
                                    <small class="text-muted">₱<?= number_format($item['unit_price'], 2) ?></small>
                                    <?php if ($item['requires_prescription']): ?>
                                        <span class="badge bg-info prescription-badge">Rx</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-4">
                                    <input type="number" class="form-control form-control-sm quantity-input" 
                                           value="<?= $item['quantity'] ?>" min="1"
                                           data-product-id="<?= $item['product_id'] ?>">
                                </div>
                                <div class="col-2 text-end">
                                    <button class="btn btn-sm btn-danger remove-item" 
                                            data-product-id="<?= $item['product_id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-12 text-end">
                                    <small>Subtotal: ₱<?= number_format($item['subtotal'], 2) ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Cart Summary -->
                <hr>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Total Amount:</strong>
                    </div>
                    <div class="col-6 text-end">
                        <h4>₱<span id="totalAmount"><?= number_format($cartTotal ?? 0, 2) ?></span></h4>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label>Amount Paid:</label>
                    </div>
                    <div class="col-6">
                        <input type="number" id="amountPaid" class="form-control" 
                               placeholder="0.00" step="0.01">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Change:</strong>
                    </div>
                    <div class="col-6 text-end">
                        <h5 class="text-success">₱<span id="changeDue">0.00</span></h5>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label>Payment Method:</label>
                    </div>
                    <div class="col-6">
                        <select id="paymentMethod" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="gcash">GCash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" id="checkoutBtn">
                        <i class="fas fa-check-circle"></i> Complete Sale
                    </button>
                    <button class="btn btn-danger" id="clearCartBtn">
                        <i class="fas fa-trash-alt"></i> Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let searchTimeout;
    
    // Search products
    function searchProducts() {
        let keyword = $('#searchProduct').val();
        
        if (keyword.length < 2) {
            $('#productsList').html(`
                <div class="text-center text-muted py-5">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p>Type at least 2 characters to search</p>
                </div>
            `);
            return;
        }
        
        $.ajax({
            url: '<?= base_url("/products/search") ?>',
            method: 'GET',
            data: { keyword: keyword },
            success: function(response) {
                if (response.length > 0) {
                    let html = '<div class="row">';
                    response.forEach(product => {
                        html += `
                            <div class="col-md-6">
                                <div class="product-card p-2" data-product-id="${product.product_id}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>${product.generic_name}</strong>
                                            ${product.brand_name ? `<br><small>${product.brand_name}</small>` : ''}
                                            <div class="mt-1">
                                                <span class="badge bg-primary">₱${parseFloat(product.unit_price).toFixed(2)}</span>
                                                <span class="badge bg-secondary">Stock: ${product.stock_quantity}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-primary add-to-cart" 
                                                    data-product-id="${product.product_id}"
                                                    data-product-name="${product.generic_name}"
                                                    data-price="${product.unit_price}">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#productsList').html(html);
                } else {
                    $('#productsList').html(`
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>No products found</p>
                        </div>
                    `);
                }
            }
        });
    }
    
    // Search on input with delay
    $('#searchProduct').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchProducts, 500);
    });
    
    $('#searchBtn').click(searchProducts);
    
    // Add to cart
    $(document).on('click', '.add-to-cart', function() {
        let productId = $(this).data('product-id');
        let quantity = 1;
        
        $.ajax({
            url: '<?= base_url("/sales/addToCart") ?>',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
    
    // Update cart quantity
    $(document).on('change', '.quantity-input', function() {
        let productId = $(this).data('product-id');
        let quantity = $(this).val();
        
        $.ajax({
            url: '<?= base_url("/sales/updateCart") ?>',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
    
    // Remove from cart
    $(document).on('click', '.remove-item', function() {
        let productId = $(this).data('product-id');
        
        Swal.fire({
            title: 'Remove item?',
            text: "This item will be removed from cart",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url("/sales/removeFromCart") ?>',
                    method: 'POST',
                    data: { product_id: productId },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });
    
    // Calculate change
    $('#amountPaid').on('input', function() {
        let total = parseFloat($('#totalAmount').text().replace(/,/g, ''));
        let paid = parseFloat($(this).val()) || 0;
        let change = paid - total;
        
        if (change < 0) change = 0;
        $('#changeDue').text(change.toFixed(2));
    });
    
    // Checkout
    $('#checkoutBtn').click(function() {
        let total = parseFloat($('#totalAmount').text().replace(/,/g, ''));
        let amountPaid = parseFloat($('#amountPaid').val()) || 0;
        let customerId = $('#customerId').val();
        let paymentMethod = $('#paymentMethod').val();
        
        if (amountPaid < total) {
            Swal.fire('Insufficient Payment', 'Amount paid is less than total', 'error');
            return;
        }
        
        if ($('#cartItems .cart-item').length === 0) {
            Swal.fire('Cart Empty', 'Please add items to cart', 'error');
            return;
        }
        
        $.ajax({
            url: '<?= base_url("/sales/checkout") ?>',
            method: 'POST',
            data: {
                customer_id: customerId,
                amount_paid: amountPaid,
                payment_method: paymentMethod
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Sale Completed!',
                        html: `Invoice: <strong>${response.invoice_number}</strong><br>
                               Total: ₱${response.total_amount}<br>
                               Change: ₱${response.change_due}`,
                        icon: 'success',
                        confirmButtonText: 'Print Receipt'
                    }).then(() => {
                        window.location.href = '<?= base_url("/sales") ?>';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
    
    // Clear cart
    $('#clearCartBtn').click(function() {
        Swal.fire({
            title: 'Clear cart?',
            text: "All items will be removed",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, clear'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url("/sales/clearCart") ?>',
                    method: 'POST',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });
});
</script>

<?= $this->endSection() ?>