<?php

namespace App\Controllers;

use App\Models\SaleModel;

class Sales extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $saleModel = new SaleModel();
        $data['sales'] = $saleModel->orderBy('sale_date', 'DESC')->findAll();
        
        return view('sales/index', $data);
    }
    
    // View a single sale transaction
    public function view($id = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $saleModel = new SaleModel();
        $sale = $saleModel->find($id);
        
        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found.');
        }
        
        $data['title'] = 'Sale Details';
        $data['sale'] = $sale;
        
        return view('sales/view', $data);
    }
}