<?php
namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'first_name',
        'last_name',
        'contact_number',
        'email',
        'address',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    protected $deletedField = false;

    // Validation rules
    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[50]|alpha_space',
        'last_name' => 'required|min_length[2]|max_length[50]|alpha_space',
        'contact_number' => 'permit_empty|min_length[11]|max_length[15]|regex_match[/^[0-9]+$/]',
        'email' => 'permit_empty|valid_email|is_unique[customers.email,customer_id,{customer_id}]',
        'address' => 'permit_empty|max_length[500]'
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required.',
            'min_length' => 'First name must be at least 2 characters.',
            'alpha_space' => 'First name can only contain letters and spaces.'
        ],
        'last_name' => [
            'required' => 'Last name is required.',
            'min_length' => 'Last name must be at least 2 characters.',
            'alpha_space' => 'Last name can only contain letters and spaces.'
        ],
        'contact_number' => [
            'min_length' => 'Contact number must be at least 11 digits.',
            'max_length' => 'Contact number cannot exceed 15 digits.',
            'regex_match' => 'Contact number can only contain numbers.'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address.',
            'is_unique' => 'This email address is already registered.'
        ]
    ];

    protected $skipValidation = false;

    // Custom methods
    
    /**
     * Get full name attribute
     */
    public function getFullName($customerId)
    {
        $customer = $this->find($customerId);
        
        if ($customer) {
            return $customer['first_name'] . ' ' . $customer['last_name'];
        }
        
        return '';
    }

    /**
     * Get customers with purchase history
     */
    public function getCustomersWithPurchaseHistory()
    {
        $db = \Config\Database::connect();
        
        return $db->table('customers')
                  ->select('customers.*, COUNT(sales.sale_id) as total_purchases, SUM(sales.total_amount) as total_spent')
                  ->join('sales', 'sales.customer_id = customers.customer_id', 'left')
                  ->groupBy('customers.customer_id')
                  ->orderBy('total_spent', 'DESC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Search customers
     */
    public function searchCustomers($keyword)
    {
        return $this->groupStart()
                        ->like('first_name', $keyword)
                        ->orLike('last_name', $keyword)
                        ->orLike('contact_number', $keyword)
                        ->orLike('email', $keyword)
                    ->groupEnd()
                    ->orderBy('first_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get recent customers (last 30 days)
     */
    public function getRecentCustomers($limit = 10)
    {
        return $this->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get top customers by total purchases
     */
    public function getTopCustomers($limit = 10)
    {
        $db = \Config\Database::connect();
        
        return $db->table('customers')
                  ->select('customers.customer_id, customers.first_name, customers.last_name, customers.contact_number, COUNT(sales.sale_id) as total_purchases, SUM(sales.total_amount) as total_spent')
                  ->join('sales', 'sales.customer_id = customers.customer_id')
                  ->where('sales.status', 'completed')
                  ->groupBy('customers.customer_id')
                  ->orderBy('total_spent', 'DESC')
                  ->limit($limit)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get customer dashboard statistics
     */
    public function getDashboardStats()
    {
        $totalCustomers = $this->countAll();
        $recentCustomers = $this->where('created_at >=', date('Y-m-d', strtotime('-30 days')))->countAllResults();
        
        $db = \Config\Database::connect();
        $avgSpending = $db->table('sales')
                          ->select('AVG(total_amount) as avg_spending')
                          ->where('status', 'completed')
                          ->get()
                          ->getRowArray();
        
        return [
            'total_customers' => $totalCustomers,
            'recent_customers' => $recentCustomers,
            'average_spending' => round($avgSpending['avg_spending'] ?? 0, 2)
        ];
    }

    /**
     * Get customer by email
     */
    public function getCustomerByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get customer by contact number
     */
    public function getCustomerByContact($contactNumber)
    {
        return $this->where('contact_number', $contactNumber)->first();
    }

    /**
     * Update customer last purchase date
     */
    public function updateLastPurchase($customerId, $saleDate)
    {
        // You can add a last_purchase_date field to customers table if needed
        // For now, this is handled by the sales table query
        return true;
    }

    /**
     * Export customers to array for CSV/Excel
     */
    public function getExportData()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get customer lifetime value
     */
    public function getCustomerLifetimeValue($customerId)
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('sales')
                     ->select('SUM(total_amount) as total_spent, COUNT(sale_id) as total_purchases')
                     ->where('customer_id', $customerId)
                     ->where('status', 'completed')
                     ->get()
                     ->getRowArray();
        
        return [
            'total_spent' => $result['total_spent'] ?? 0,
            'total_purchases' => $result['total_purchases'] ?? 0,
            'average_purchase' => ($result['total_purchases'] > 0) ? ($result['total_spent'] / $result['total_purchases']) : 0
        ];
    }
}