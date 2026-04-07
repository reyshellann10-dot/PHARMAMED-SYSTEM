<?php
namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\SaleModel;

class Customers extends BaseController
{
    protected $customerModel;
    protected $saleModel;
    protected $session;
    
    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->saleModel = new SaleModel();
        $this->session = \Config\Services::session();
        
        // Check if user is logged in
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
    }
    
    // Display customers list
    public function index()
    {
        $data['title'] = 'Customer Management';
        $data['customers'] = $this->customerModel
            ->orderBy('created_at', 'DESC')
            ->paginate(15);
        
        $data['pager'] = $this->customerModel->pager;
        
        // Get total customers count
        $data['totalCustomers'] = $this->customerModel->countAll();
        
        // Get recent customers (last 30 days)
        $data['recentCustomers'] = $this->customerModel
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->countAllResults();
        
        return view('customers/index', $data);
    }
    
    // Show create customer form
    public function create()
    {
        $data['title'] = 'Add New Customer';
        
        return view('customers/create', $data);
    }
    
    // Store new customer
    public function store()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'contact_number' => 'permit_empty|min_length[11]|max_length[15]',
            'email' => 'permit_empty|valid_email|is_unique[customers.email]',
            'address' => 'permit_empty|max_length[500]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'first_name' => ucwords($this->request->getPost('first_name')),
            'last_name' => ucwords($this->request->getPost('last_name')),
            'contact_number' => $this->request->getPost('contact_number'),
            'email' => strtolower($this->request->getPost('email')),
            'address' => $this->request->getPost('address'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->customerModel->insert($data)) {
            return redirect()->to('/customers')
                ->with('success', 'Customer added successfully!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add customer.');
        }
    }
    
    // Show edit customer form
    public function edit($id = null)
    {
        $data['title'] = 'Edit Customer';
        $data['customer'] = $this->customerModel->find($id);
        
        if (!$data['customer']) {
            return redirect()->to('/customers')
                ->with('error', 'Customer not found.');
        }
        
        return view('customers/edit', $data);
    }
    
    // Update customer
    public function update($id = null)
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'contact_number' => 'permit_empty|min_length[11]|max_length[15]',
            'email' => 'permit_empty|valid_email|is_unique[customers.email,customer_id,' . $id . ']',
            'address' => 'permit_empty|max_length[500]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'first_name' => ucwords($this->request->getPost('first_name')),
            'last_name' => ucwords($this->request->getPost('last_name')),
            'contact_number' => $this->request->getPost('contact_number'),
            'email' => strtolower($this->request->getPost('email')),
            'address' => $this->request->getPost('address')
        ];
        
        if ($this->customerModel->update($id, $data)) {
            return redirect()->to('/customers')
                ->with('success', 'Customer updated successfully!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update customer.');
        }
    }
    
    // Delete customer
    public function delete($id = null)
    {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            return redirect()->to('/customers')
                ->with('error', 'Customer not found.');
        }
        
        // Check if customer has sales records
        $salesCount = $this->saleModel
            ->where('customer_id', $id)
            ->countAllResults();
        
        if ($salesCount > 0) {
            return redirect()->to('/customers')
                ->with('error', 'Cannot delete customer with ' . $salesCount . ' transaction(s).');
        }
        
        if ($this->customerModel->delete($id)) {
            return redirect()->to('/customers')
                ->with('success', 'Customer deleted successfully!');
        } else {
            return redirect()->to('/customers')
                ->with('error', 'Failed to delete customer.');
        }
    }
    
    // View customer details and purchase history
    public function view($id = null)
    {
        $data['customer'] = $this->customerModel->find($id);
        
        if (!$data['customer']) {
            return redirect()->to('/customers')
                ->with('error', 'Customer not found.');
        }
        
        $data['title'] = 'Customer Details - ' . $data['customer']['first_name'] . ' ' . $data['customer']['last_name'];
        
        // Get customer purchase history
        $data['purchases'] = $this->saleModel
            ->select('sales.*, users.full_name as cashier_name')
            ->join('users', 'users.user_id = sales.user_id')
            ->where('sales.customer_id', $id)
            ->orderBy('sales.sale_date', 'DESC')
            ->findAll();
        
        // Calculate total spent
        $data['totalSpent'] = $this->saleModel
            ->select('SUM(total_amount) as total')
            ->where('customer_id', $id)
            ->where('status', 'completed')
            ->first();
        
        $data['totalSpent'] = $data['totalSpent']['total'] ?? 0;
        
        // Get total purchases count
        $data['totalPurchases'] = count($data['purchases']);
        
        // Get last purchase date
        $lastPurchase = $this->saleModel
            ->where('customer_id', $id)
            ->orderBy('sale_date', 'DESC')
            ->first();
        
        $data['lastPurchaseDate'] = $lastPurchase ? $lastPurchase['sale_date'] : 'N/A';
        
        return view('customers/view', $data);
    }
    
    // Search customers (AJAX)
    public function search()
    {
        $keyword = $this->request->getGet('keyword');
        
        if (!$keyword) {
            return $this->response->setJSON([]);
        }
        
        $customers = $this->customerModel
            ->select('customer_id, first_name, last_name, contact_number')
            ->groupStart()
                ->like('first_name', $keyword)
                ->orLike('last_name', $keyword)
                ->orLike('contact_number', $keyword)
            ->groupEnd()
            ->orderBy('first_name', 'ASC')
            ->limit(10)
            ->find();
        
        return $this->response->setJSON($customers);
    }
    
    // Export customers to CSV
    public function export()
    {
        $customers = $this->customerModel->findAll();
        
        $filename = 'customers_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, ['ID', 'First Name', 'Last Name', 'Contact Number', 'Email', 'Address', 'Registered Date']);
        
        // Add data
        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['customer_id'],
                $customer['first_name'],
                $customer['last_name'],
                $customer['contact_number'],
                $customer['email'],
                $customer['address'],
                $customer['created_at']
            ]);
        }
        
        fclose($output);
        exit();
    }
}