<?php

namespace App\Http\Controllers;
use App\Models\EquipmentPurchase;
use Illuminate\Http\Request;

class AdminPurchaseController extends Controller

{



    public function index()
    {
        $purchases = EquipmentPurchase::with(['customer', 'location', 'equipment'])
            ->orderByRaw('YEAR(paid_at) ASC')
            ->orderByRaw('MONTH(paid_at) ASC')
            ->orderBy('paid_at', 'ASC')
            ->get();

        return view('admin.purchase.index', compact('purchases'));
    }



    //     public function show($id)
    // {
    //     $purchase = EquipmentPurchase::with(['customer', 'location', 'equipment'])->findOrFail($id);

    //     return view('admin.purchase.show', compact('purchase'));
    // }


    public function show($id)
    {
        $purchase = EquipmentPurchase::with(['customer', 'location', 'equipment'])->findOrFail($id);
        return view('admin.purchase.show', compact('purchase'));
    }

}
